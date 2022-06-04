<?php

namespace App\Http\Controllers\Api\v2;

use App\Classes\Helper;
use App\Classes\OzKeyRequestHandler;
use App\Constants\Errors\ProductsErrors;
use App\Constants\OzonConstants;
use App\Constants\OzonFeaturesConstants;
use App\Constants\References\ProductStatusesConstants;
use App\DTO\Ozon\ProductDetailDTO;
use App\Exceptions\Ozon\OzonApiException;
use App\Exceptions\Ozon\OzonServerException;
use App\Exceptions\Product\ProductException;
use App\Http\Controllers\Api\Ozon\ProductsController as NewOzonProductController;
use App\Http\Controllers\Controller;
use App\Http\Requests\OzProductRequest;
use App\Http\Resources\Ozon\ProductDetailResource;
use App\Jobs\CheckingProductChanges;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\UpdateOzOptimisation;
use App\Models\Feature;
use App\Models\OzCategory;
use App\Models\OzFeatureOption;
use App\Models\OzListGoodsAdd;
use App\Models\OzListGoodsUser;
use App\Models\OzProduct;
use App\Models\OzProductFeature;
use App\Models\OzProductStatus;
use App\Models\OzTemporaryProduct;
use App\Models\Product;
use App\Models\ProductChangeHistory;
use App\Models\ProductFeatureHistory;
use App\Models\ProductPositionHistory;
use App\Models\ProductPositionHistoryGraph;
use App\Models\ProductPriceChangeHistory;
use App\Services\Escrow\EscrowService;
use App\Services\UserService;
use App\Services\V2\FtpService;
use App\Services\V2\OptimisationHistoryService;
use App\Services\V2\OzonApi;
use App\Services\V2\ProductServiceCreatorImporter;
use App\Services\V2\ProductServiceImporter;
use App\Services\V2\ProductServiceUpdater;
use App\Services\V2\ProductTrackingService;
use Barryvdh\DomPDF\Facade as PDF;
use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Class ProductsController
 * Контроллер для управления товарами
 * @package App\Http\Controllers\Api\v2
 */
class ProductsController extends Controller
{
    private function setRequest(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->user = $request->get('user');
        $this->page = abs((int)$request->page) ?: 1;
        if (app()->runningInConsole() === false && $this->user) {
            if ($request->perPage) {
                $this->pagination = (int)$request->perPage;
                Cache::put($this->user['id'] . $this->paginationPostfix, $this->pagination);
            } elseif (Cache::has($this->user['id'] . $this->paginationPostfix)) {
                $this->pagination = Cache::get($this->user['id'] . $this->paginationPostfix);
            }
        }
    }

    /**
     * Список временных (пустых) товаров с сортировкой
     *
     * @param string $sortType
     * @param string $sortBy
     * @return mixed
     */
    private static function getSortedTempProducts(string $sortType, $sortBy)
    {
        $availableFields = array_merge(Schema::getColumnListing((new OzTemporaryProduct())->getTable()),
            ['name', 'url']);
        $sortField = ($sortBy && in_array($sortBy, $availableFields))
            ? $sortBy : 'updated_at';
        $url = OzonConstants::PRODUCT_DETAIL_URL;

        return OzTemporaryProduct::currentUser()
            ->selectRaw("oz_temporary_products.*, sku_fbo as name, CONCAT('$url', sku_fbo) as url")
            ->orderBy($sortField, $sortType);
    }

    /**
     * Массовая загрузка товара
     *
     * @param Request $request
     * @return mixed
     * @throws OzonServerException
     * @throws ProductException
     */
    public function massAddProducts(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);

        $request->validate([
            'skus' => ['required', 'string', 'min:9'],
        ]);

        $inputProducts = preg_split('/[,\s]+/', $request->skus);

        if (count($inputProducts) > 10) {
            return response()->api_fail(
                'Допускается ввод не более 10 товаров',
                [],
                422,
                ProductsErrors::TOO_MANY_SKUS
            );
        }

        ModelHelper::transaction(function () use (&$products, &$productsSaved, $inputProducts) {
            $products = [];
            $productsSaved = false;
            $productTrackingService = new ProductTrackingService();

            foreach ($inputProducts as $key => $input) {
                if (!preg_match('/^https:\/\/www\.ozon\.ru\/context\/detail\/id\/(\d{9})\/?$|^(\d{9})$/', $input, $sku)) {
                    $products[$key]['sku'] = $input;
                    $products[$key]['code'] = ProductsErrors::WRONG_SKU_FORMAT;
                    $products[$key]['message'] = 'Неверный формат введенных данных';
                    continue;
                }

                $sku = end($sku);

                $products[$key]['sku'] = $sku;

                try {
                    $productTrackingService->trackProduct($sku);
                    $products[$key]['code'] = 200;
                    $products[$key]['message'] = 'Успешно добавлен';
                    $productsSaved = true;
                } catch (OzonApiException | ProductException $exception) {
                    report($exception);
                    $products[$key]['code'] = $exception->getCode();
                    $products[$key]['message'] = $exception->getMessage();
                    continue;
                } catch (Exception $exception) {
                    DB::rollBack();
                    throw $exception;
                }
            }
        });

        if ($productsSaved) {
            try {
                (new FtpService())->sendSkuRequestFile();
            } catch (Exception $exception) {
                report($exception);
                ExceptionHandlerHelper::logFail($exception);
            }
        }

        return response()->api_success(['items' => $products], 200);
    }

    /**
     * Добавление товара
     *
     * @param Request $request
     * @return mixed
     * @throws ProductException|OzonServerException
     */
    public function addProduct(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        //Принимает на вход ску или ссылку на карточку товара в Озон
        $request->validate([
            'sku' => ['required', 'regex:/^https:\/\/www\.ozon\.ru\/context\/detail\/id\/(\d{9})\/?$|^(\d{9})$/'],
        ]);

        if (!UserService::canCreateProduct()) {
            return response()->api_fail(
                'На бесплатном тарифе можно добавить только три товара для отслеживания',
                [],
                422,
                ProductsErrors::TEST_PRODUCT_LIMIT
            );
        }

        preg_match('/^https:\/\/www\.ozon\.ru\/context\/detail\/id\/(\d{9})\/?$|^(\d{9})$/', $request->sku, $sku);
        $sku = end($sku);
        $productTrackingService = new ProductTrackingService();
        $productTrackingService->trackProduct($sku);

        try {
            (new FtpService())->sendSkuRequestFile();
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        return response()->api_success([], 201);
    }

    /**
     * Изменение товара
     *
     * @throws Exception
     */
    public function modifyProduct(OzProductRequest $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $account = UserService::getCurrentAccount();

        if (!is_array($account)) {
            return $account;
        }

        if (!$account['platform_client_id']) {
            return response()->api_fail(
                'Установите API ключ для сбора данных на странице "Настройки"', [], 422, AuthErrors::EMPTY_API_TOKEN
            );
        }

        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentAccount($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        // check existing product in OZON
        $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $productInfoResponse = $ozonApiClient->ozonRepeat('getProductInfo', $product->sku_fbo);
        if (empty($productInfoResponse)) {
            return response()->api_fail(OzonApiException::MESSAGES[404], [], 404);
        }

        $characteristics = $request->characteristics;

        // Ищем в товаре характеристике "Объединить на одной карточке" и если не заполнена отправляем рандомную строку
        if (isset($characteristics)) {
            foreach ($characteristics as $key => $characteristic) {
                if (in_array($characteristic['id'], OzProduct::JOIN_CARD_CHARACRESTICS_IDS) && empty($characteristic->value)) {
                    $characteristics[$key]['value'] = Str::random(6);
                    break;
                }
            }
        }

        // Add descriptions
        if ($request->descriptions) {
            $characteristics[] = [
                'id' => OzProduct::PRODUCT_DESCRIPTION_FEATURE_ID,
                'value' => $request->descriptions,
            ];
        }

        // Add youtubecodes
        if ($request->youtubecodes) {
            $characteristics[] = [
                'id' => OzProduct::PRODUCT_YOUTUBE_FEATURE_ID,
                'value' => $request->youtubecodes,
            ];
        } else {
            $characteristics[] = [
                'id' => OzProduct::PRODUCT_YOUTUBE_FEATURE_ID,
                'value' => '',
            ];
        }

        $requiredCharacteristics = $product->category->features
            ->where('is_required', true)
            ->whereNotIn('id', OzonFeaturesConstants::NOT_FOR_NOT_FOR_VALIDATE)
            ->pluck('name', 'id')
            ->toArray();

        $filledRequiredCharacteristics = $product->featuresValues
            ->whereIn('feature_id', array_keys($requiredCharacteristics))
            ->pluck('feature.name', 'feature_id')
            ->toArray();

        if ($characteristics) {
            $filledRequiredCharacteristicsIds = array_merge(
                array_column($characteristics, 'id'),
                array_keys($filledRequiredCharacteristics)
            );
        } else {
            $filledRequiredCharacteristicsIds = array_keys($filledRequiredCharacteristics);
        }

        //проверяем нет ли незаполненных характеристик
        $unfilled_requied_characteristics = array_diff(
            array_keys($requiredCharacteristics),
            $filledRequiredCharacteristicsIds
        );

        if ($unfilled_requied_characteristics) {
            $unfilledRequiredFeatures = [];
            foreach ($unfilled_requied_characteristics as $unfilled) {
                $unfilledRequiredFeatures[][$unfilled] = 'Характеристика "' . $requiredCharacteristics[$unfilled] . '" обязательна для заполнения';
            }

            return response()->api_fail(
                'Необходимо заполнить обязательные характеристики',
                $unfilledRequiredFeatures,
                422,
                ProductsErrors::NOT_FILLED_REQUIRED_CHARACTERISTICS
            );
        }

        // Update in Ozon table
        $product->dimension_unit = $request->dimension_unit ?? 'mm';
        $product->weight_unit = $request->weight_unit ?? 'g';
        $product->depth = $request->depth ?? 0;
        $product->height = $request->height ?? 0;
        $product->width = $request->width ?? 0;
        $product->weight = $request->weight ?? 0;
        $product->name = $request->name;
        $product->marketing_price = $request->marketing_price;
        $product->old_price = $request->old_price;
        $product->premium_price = $request->premium_price;
        $product->price = $request->price;
        $product->images = $request->images;
        // todo store in database in table
        $product->images360 = $request->images360 ?? [];
        $product->color_image = $request->colorSample;

        $product->save();

        ModelHelper::transaction(function () use ($product, $request, $id, $characteristics) {
            $productHistory = new ProductChangeHistory();
            $productHistory->product_id = $product->id;
            $productHistory->name = $request->name ?: $product->name;
            $productHistory->status_id = OzProductStatus::where('code',
                ProductStatusesConstants::MODERATION_CODE)->first()->id;
            $productHistory->is_send = false;
            $productHistory->save();

            //изменим цену и старую цену товара
            if ($request->price || $request->old_price) {
                $productPriceHistory = new ProductPriceChangeHistory();
                $productPriceHistory->price = $request->price;
                $productPriceHistory->old_price = $request->old_price;
                $productPriceHistory->product_id = $product->id;
                $productPriceHistory->product_history_id = $productHistory->id;


                $productPriceHistory->save();
                $request->price && $product->price = $request->price;
                $request->old_price && $product->old_price = $request->old_price;
                $product->save();
            }

            //Если изменяем хар-ки, то для каждой из них создадим запись в табличке с историей
            if ($characteristics) {

                foreach ($characteristics as $characteristic) {
                    if (!isset($characteristic['value']) && isset($characteristic['selected_options'])) {
                        $characteristic['value'] = $characteristic['selected_options'];
                    }

                    if (isset($characteristic['value']) && $characteristic['value'] == null) {
                        $characteristic['value'] = '';
                    }

                    if (!isset($characteristic['value'])) {
                        continue;
                    }

                    $characteristicValues = $characteristic['value'];
                    if (!is_array($characteristic['value'])) {
                        $characteristicValues = [$characteristic['value']];
                    }
                    foreach ($characteristicValues as $characteristicValue) {
                        if ($characteristicValue) {
                            $productFeatureHistory = new ProductFeatureHistory();
                            $productFeatureHistory->history_id = $productHistory->id;
                            $productFeatureHistory->feature_id = $characteristic['id'];
                            $productFeatureHistory->value = $characteristicValue;
                            $productFeatureHistory->created_at = NOW();
                            $productFeatureHistory->is_send = true;
                            $productFeatureHistory->save();
                        }
                    }
                }
            }

            //актуализируем список в таблице OzListGoodsUser
            $ozPickList = OzListGoodsAdd::select('oz_product_id', 'key_request', 'popularity')
                ->where('oz_product_id', $id)
                ->get();

            $ozUsingKeywordToSave = $ozPickList->toArray();
            $ozUsingKeywordToSave = array_map(function ($element) {
                $element['created_at'] = now();
                return $element;
            }, $ozUsingKeywordToSave);

            OzListGoodsUser::where('oz_product_id', $id)->delete();

            OzListGoodsUser::insert($ozUsingKeywordToSave);

            OzListGoodsAdd::where('oz_product_id', $id)->delete();

            //отправляем изменения в озон
            $importer = new ProductServiceImporter($productHistory->id);
            if (!empty($productInfoResponse['data']['result'])) {
                $importer->productInfo = $productInfoResponse['data']['result'];
            }
            $importer->send();

            $product->status_id = OzProductStatus::where('code',
                ProductStatusesConstants::MODERATION_CODE)->first()->id;

            // Обновление продукта
            $productServiceUpdate = new ProductServiceUpdater($product->id);
//            $productServiceUpdate->updateInfo();  // пока не трогать возможно есть нереализованные возможности
            $productServiceUpdate->updateStats();
            $productServiceUpdate->updateFeatures(); // Какой то бред то записываем продукты то обновляем

            DB::commit();

            $product->brand = Helper::ozCardGetBrandFromCharacteristics($product->characteristics);
            $product->save();

            UpdateOzOptimisation::dispatch($product)->afterCommit();
            // Добавляем в очередь команду, которая будет проверять статус товара. 5 мин, так как озон не сразу обновляет статус
            CheckingProductChanges::dispatch($product, request()->user ?? null)->delay(now()->addMinutes(5));

            DashboardAccountUpdateJob::dispatch(
                UserService::getUserId(),
                UserService::getAccountId(),
                UserService::getCurrentAccountPlatformId()
            )->delay(now()->addMinute(7));

        });
        return response()->api_success([], 200);
    }

    /**
     * Получение списка товаров
     *
     * @param Request $request
     * @return mixed
     */
    public function getProductsList(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        //если есть временные товары, то добавляем их в отслеживаемые
        $loadedProducts = [];
        $currentAccount = UserService::getCurrentAccount();

        if (!is_array($currentAccount)) {
            return response()->api_fail(__('Not set active Ozon account!'), [], 403);
        }

        $ozonApiKey = $currentAccount['platform_api_key'] ?? false;
        $this->searchFilter = $request->get('search');
        $categoryId = $request->get('categoryId');
        $brand = $request->get('brand');
        $sortType = ($request->sortType and in_array($request->sortType, ['asc', 'desc']))
            ? $request->sortType : 'asc';
        $showAdd = false;

        $products = $this->getSortedProducts($request, $ozKeyRequestHandler, $sortType, $request->get('sortBy'),
            $this->searchFilter, $categoryId, $brand);

        $paginateProducts = PaginatorHelper::addPagination($request, $products);

        $deletedCount = OzProduct::where('account_id', $currentAccount)->onlyTrashed()->count('id');

        $res = [];
        $res['data'] = $this->formatProducts($request, $ozKeyRequestHandler, $paginateProducts);

        $res['last_page'] = $paginateProducts->lastPage();
        $res['total'] = $paginateProducts->total();
        $res['current_page'] = $paginateProducts->currentPage();
        $res['per_page'] = $paginateProducts->perPage();
        $res['show_advert'] = $showAdd;
        $res['loaded_products'] = $loadedProducts;
        $res['temp_products'] = !$ozonApiKey;
        $res['deletedCount'] = $deletedCount;

        return response()->api_success($res);
    }

    /**
     * Возвращает массив доступных полей для сортировки
     * Если значение не равно null, то его следует использовать для дальнейшей сортировки
     *
     * @return array
     */
    private function getAvailableSortFields(): array
    {
        $ozProductModel = new OzProduct();
        return array_merge(
            array_fill_keys($ozProductModel->getFillable(), null),
            [
                'category'          => null,
                'characteristics'   => null,
                'position'          => null,
                'reviews'           => 'count_reviews',
                'status'            => 'status_id',
            ]
        );
    }

    /**
     * Список отслеживаемых товаров с сортировкой
     *
     * @param Request $request
     * @param OzKeyRequestHandler $ozKeyRequestHandler
     * @param string $sortType
     * @param string|null $sortBy
     * @param $searchFilter
     * @param $categoryId
     * @param $brand
     * @return mixed
     * @throws Exception
     */
    private function getSortedProducts(
        Request $request,
        OzKeyRequestHandler $ozKeyRequestHandler,
        string $sortType,
        ?string $sortBy,
        $searchFilter = '',
        $categoryId = null,
        $brand = null
    ) {
        $this->setRequest($request, $ozKeyRequestHandler);

        self::checkAndDeactivateProductQty();

        $userProducts = OzProduct::currentUserAndAccount()->select(((new OzProduct)->getTable()) . '.*')->search($searchFilter);

        if ($categoryId) {
            if (is_array($categoryId)) {
                $categoryIds = $categoryId;
            } else {
                $categoryIds = explode(',', $categoryId);
            }
            $userProducts->whereHas('category', function ($query) use ($categoryIds) {
                return $query->whereIn('id', $categoryIds);
            });
        } else {
            $userProducts->with('category');
        }

        if ($brand) {
            $userProducts->searchBrand($brand);
        }

        $sortField = 'updated_at';
        $availableFields = $this->getAvailableSortFields();

        if ($sortBy !== null && array_key_exists($sortBy, $availableFields)) {
            $sortField = $availableFields[$sortBy] !== null ? $availableFields[$sortBy] : $sortBy;
        }

        switch ($sortField) {
            case 'position':
                $products = $userProducts->addSelect([
                    //Берет последнюю не нулевую позицию
                    'lastPosition' =>
                        ProductPositionHistory::query()
                            ->select('position')
                            ->whereColumn('product_id', 'oz_products.id')
                            ->whereNotNull('position')
                            ->orderBy('date', 'desc')
                            ->orderBy('updated_at', 'desc')
                            ->take(1),
                ]);

                $products->orderBy('lastPosition', $sortType);
                break;
            case 'category':
                $products = $userProducts->select('oz_products.*')
                    ->join('oz_categories', 'oz_categories.id', '=', 'oz_products.category_id');


                $products->orderBy('oz_categories.name', $sortType);
                break;
            case 'characteristics':
                $products = $userProducts->withCount([
                    'featuresValues' => function (Builder $q) {
                        $q->select(DB::raw('count(distinct(feature_id))'));
                    },
                ]);


                $products->orderBy('features_values_count', $sortType);
                break;
            default:
                $products = $userProducts;

                $products->orderBy($sortField, $sortType);
                break;
        }

        return $products;
    }

    /**
     * Приведение отслеживаемых товаров к формату ответа
     *
     * @param Request $request
     * @param OzKeyRequestHandler $ozKeyRequestHandler
     * @param $products
     * @return array
     */
    private function formatProducts(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $products): array
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $productList = [];
        foreach ($products as $product) {
            $productList[] = [
                'id' => $product->id,
                'external_id' => $product->external_id,
                'sku' => [
                    'fbo' => $product->sku_fbo,
                    'fbs' => $product->sku_fbs
                ],
                'name' => $product->actual_name,
                'category' => [
                    'id' => optional($product->category)->id,
                    'name' => optional($product->category)->name,
                    'external_id' => optional($product->category)->external_id,
                    'parent_id' => optional($product->category)->parent_id,
                ],
                'brand' => $product->brand,
                'position' => $product->position,
                'optimization' => $product->optimization,
                'rating' => $product->rating,
                'reviews' => $product->count_reviews,
                'characteristics' => $product->characteristics_count,
                'updated_at' => $product->updated_at,
                'price' => number_format($product->price, 2),
                'old_price' => $product->old_price ? number_format($product->old_price, 2) : 0,
                'premium_price' => $product->premium_price ? number_format($product->premium_price, 2) : 0,
                'photo_url' => $product->photo_url ?? $product->images[0] ?? '',
                'product_ozon_url' => $product->url,
                'status_id' => $product->status->id,
                'status' => $product->status->name,
                'triggers' => [],
                'card_updated' => $product->card_updated,
                'is_test' => $product->is_test,
                'characteristics_updated_at' => $product->characteristics_updated_at,
                'characteristics_updated' => $product->characteristics_updated,
                'position_updated' => $product->position_updated,
                'mpstat_updated_at' => $product->mpstat_updated_at,
                'web_category_parsed_at' => null,
                // Todo тоже самое всё в JOB и расчет
                'content_optimization' => $product->content_optimization,
                'search_optimization' => $product->search_optimization,
                'visibility_optimization' => $product->visibility_optimization,
                'quantity' => [
                    'fbo' => $product->quantity_fbo,
                    'fbs' => $product->quantity_fbs,
                ],
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $productList;
    }

    /**
     * Получение детализации товара
     *
     * @param Request $request
     * @param OzKeyRequestHandler $ozKeyRequestHandler
     * @param EscrowService $escrowService
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function getProductDetail(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, EscrowService $escrowService, $id): mixed
    {
        $this->setRequest($request, $ozKeyRequestHandler);

        /**
         * @var OzProduct $product
         */
        $product = OzProduct::currentUserAndAccount()->firstWhere('id', $id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $webCategoryHistory = $product->webCategory
            ? $product->webCategory->history()->latest()->first()
            : null;
        $webCategoryHistoryCreatedAt = $webCategoryHistory->created_at ?? null;

        $product->load('changeHistory.priceChanges');

        $history = $product->changeHistory->sortByDesc('created_at')->first();

        $priceError = ($history && $history->priceChanges->count())
            ? $history->priceChanges->sortByDesc('created_at')->first()->errors
            : null;

        // Поисковая оптимизация
        $ozProductNew = new NewOzonProductController($request, $ozKeyRequestHandler);
        $listGoodsAdds = $ozProductNew->getListGoodsAdds($request, $ozKeyRequestHandler, $id);
        $analyticsData = $product->positionAnalytics()
            ->select(DB::raw('
                AVG(hits_view) as avg_hits_view,
                AVG(conv_tocart_pdp) as avg_conv_tocart_pdp,
                AVG(conv_tocart) as avg_conv_tocart,
                SUM(revenue) as sum_revenue,
                SUM(ordered_units) as sum_ordered_units,
                AVG(position_category) as avg_position_category
            '))
            ->orderByDesc('report_date')
            ->take(30)
            ->get();

        $productDetailDTO = new ProductDetailDTO([
            'product' => $product,
            'analyticsData' => $analyticsData,
            'listGoodsAdds' => $listGoodsAdds,
            'escrowService' => $escrowService,
        ]);
        return response()->api_success(new ProductDetailResource($productDetailDTO));
    }

    /**
     * Шаблон детализации товара
     *
     * @return string
     */
    public function getSampleProductDetail(Request $request, OzKeyRequestHandler $ozKeyRequestHandler): string
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $files = File::allFiles(resource_path('sampleProducts'));

        return $files[array_rand($files)]->getContents();
    }

    /**
     * Получение значений словаря Ozon по его id
     * @param Request $request
     * @param int $id
     */
    public function getFeatureValues(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        // todo пока не сделано так как нет необходимости
    }

    /**
     * Поиск значений характеристики-справочника для товара
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function searchFeatureValues(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $request->validate([
            'characteristic_id' => ['required', 'integer'],
        ]);
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        /**
         * @var Feature $feature
         */
        $feature = $product->category->features->where('id', $request->characteristic_id)->first();

        if (!$feature) {
            return response()->api_fail(
                'Характеристика не найдена',
                [],
                404,
                ProductsErrors::CHARACTERISTIC_NOT_FOUND
            );
        }

        if (!$feature->count_values) {
            return response()->api_fail(
                'У характеристики не указаны возможные значения',
                [],
                404,
                ProductsErrors::CHARACTERISTIC_OPTION_NOT_FOUND
            );
        }

        $featureOptions = $feature->options()
            ->where('value', 'like', '%' . $request->value . '%')
            ->orderBy('value')
            ->take(100)->get()
            ->pluck('value', 'id')->toArray();

        $response = [];
        foreach ($featureOptions as $key => $featureOption) {
            $response[] = [$key => $featureOption];
        }

        return response()->api_success($response);
    }

    /**
     * Product delete
     * @param Request $request
     * @return mixed
     */
    public function removeProducts(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);
        $userSelectedProducts = OzProduct::select(['oz_products.id', 'oz_products.external_id'])
            ->currentUserAndAccount()->whereIn('oz_products.id', $request->ids);
        $userProductsIds = $userSelectedProducts->pluck('id');
        $userProductsExternalIds = $userSelectedProducts->pluck('external_id');

        if (!count($userProductsIds)) {
            return response()->api_fail(
                count($request->ids) == 1 ? 'Товар не найден' : 'Товары не найдены',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        // Delete and deactivate
        $this->destroyProductPositionHistory($request, $ozKeyRequestHandler, $userProductsIds);

        foreach ($userProductsExternalIds as $userProductsExternalId) {
            $createdProduct = OzProduct::where(['oz_products.external_id' => $userProductsExternalId])->first();
            if (!$createdProduct) {
                continue;
            }
            $createdProduct->detachUserAndAccount(UserService::getUserId(), UserService::getAccountId());
            //Delete where
            if (DB::table('oz_product_user')->where([
                    'external_id' => $userProductsExternalId
                ])->whereNull('deleted_at')->count() === 0) {
                DB::table('oz_product_user')->where([
                    'external_id' => $userProductsExternalId])->delete();

                OzProduct::query()->where(['external_id' => $userProductsExternalId])->delete();
            }
        }
        // Restore deleted in temporary table
        OzTemporaryProduct::currentAccount()->whereIn('external_id', $userProductsExternalIds)
            ->withTrashed()->restore();

        DashboardAccountUpdateJob::dispatch(
            UserService::getUserId(),
            UserService::getAccountId(),
            UserService::getCurrentAccountPlatformId()
        )->delay(now()->addMinute(2));

        return response()->api_success([], 203);
    }

    /**
     * Удаление записи из таблички с историей товара
     * @param \Illuminate\Support\Collection $productIds
     */
    private function destroyProductPositionHistory(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, \Illuminate\Support\Collection $productIds)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        ProductPositionHistory::query()->whereIn('product_id', $productIds)->delete();
        ProductPositionHistoryGraph::query()->whereIn('product_id', $productIds)->delete();
    }

    /**
     * Обновление товара
     *
     * @param Request $request
     * @return mixed
     */
    public function updateProducts(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        /**
         * @var OzProduct[] $userProducts
         */
        $userProducts = OzProduct::currentUser()->whereIn('id', $request->ids);

        if (!count($userProducts)) {
            return response()->api_fail(
                count($request->ids) == 1 ? 'Товар не найден' : 'Товары не найдены',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        foreach ($userProducts as $product) {
            $productServiceUpdate = new ProductServiceUpdater($product->id, UserService::getAccountId());
            $productServiceUpdate->updateInfo();
            $productServiceUpdate->updateStats();
            $productServiceUpdate->updateFeatures();
        }

        return response()->api_success([], 202);
    }

    /**
     * Генерация для списка необходимых полей для создания товара
     * @param $categoryId
     */
    public function createProductView(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $categoryId)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $characteristics = OzCategory::firstWhere('id', $categoryId)->features()->get();

        $characteristics->each(function ($characteristic) {
            if ($characteristic->is_required === 1) {
                return $characteristic;
            }
        });

        $params = [
            'characteristics' => $characteristics,
            [
                'barcode' => [
                    'is_required' => 1,
                    'max' => 30,
                    'min' => 9,
                    'type' => 'String',
                    'description' => 'Баркод - его можно сгенерировать на нашей платформе',
                ],
                'category_id' => [
                    'is_required' => 1,
                    'type' => 'Integer',
                    'description' => 'Id категории Ozon (тип товара)',
                ],
                'depth' => [
                    'is_required' => 1,
                    'type' => 'Integer',
                    'description' => 'Длина товара в упаковке',
                ],
                'dimension_unit' => [
                    'is_required' => 1,
                    'type' => 'String',
                    'description' => 'Единица измерения размеров mm/sm (латиницей)',
                ],
                'height' => [
                    'is_required' => 1,
                    'type' => 'Integer',
                    'description' => 'Высота товара в упаковке',
                ],
                'images' => [
                    'is_required' => 1,
                    'type' => 'multiline',
                    'description' => 'Массив со ссылками на изображения (первой идет главная картинка)',
                ],
                'name' => [
                    'is_required' => 1,
                    'max' => 200,
                    'min' => 10,
                    'type' => 'String',
                    'description' => 'Наименование товара',
                ],
                'offer_id' => [
                    'is_required' => 1,
                    'max' => 200,
                    'min' => 10,
                    'type' => 'String',
                    'description' => 'Номер предложение служит для объединения товара на одной карточке товара, если не хотите товар объединять суда необходимо поставить уникальное значение, например баркод сгенерируйте',
                ],
                'old_price' => [
                    'is_required' => 0,
                    'type' => 'Decimal',
                    'description' => 'Старая цена товара',
                ],
                'price' => [
                    'is_required' => 1,
                    'type' => 'Decimal',
                    'description' => 'Цена товара',
                ],
                'vat' => [
                    'is_required' => 1,
                    'type' => 'String',
                    'description' => 'НДС товара указывается как "0.2" для НДС 20%, "0.1" для НДС 10%, "0.0" для ндс 0%,',
                ],
                'weight' => [
                    'is_required' => 1,
                    'type' => 'Decimal',
                    'description' => 'Вес товара',
                ],
                'weight_unit' => [
                    'is_required' => 1,
                    'min' => 1,
                    'max' => 5,
                    'type' => 'String',
                    'description' => 'Единица веса товар kg, g',
                ],
                'width' => [
                    'is_required' => 1,
                    'type' => 'Integer',
                    'description' => 'Ширина товара в упаковке',
                ],
            ],
        ];

        return response()->json($params);
    }

    /**
     * Создание товара в Ozon
     *
     * @param Request $request
     * @return mixed
     */
    public function createProduct(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $categoryId)
    {
        $this->setRequest($request, $ozKeyRequestHandler);

        $category = OzCategory::findOrFail($categoryId);

        $request->validate([
            'name' => ['string'],
        ]);

        $request->validate([
            'price' => ['numeric', 'min:1', 'max:2147483648'],
        ]);

        $request->validate([
            'old_price' => ['numeric', 'min:1', 'max:2147483648'],
        ]);

        $request->validate([
            'premium_price' => ['numeric', 'min:1', 'max:2147483648'],
        ]);

        $request->validate([
            'buybox_price' => ['numeric', 'min:1', 'max:2147483648'],
        ]);

        $request->validate([
            'marketing_price' => ['numeric', 'min:1', 'max:2147483648'],
        ]);

        $request->validate([
            'discount' => ['numeric', 'min:0', 'max:99'],
        ]);

        $request->validate([
            'depth' => ['numeric', 'min:0', 'max:2147483648'],
        ]);

        $request->validate([
            'height' => ['numeric', 'min:0', 'max:2147483648'],
        ]);


        $request->validate([
            'weight' => ['numeric', 'min:0', 'max:2147483648'],
        ]);

        $request->validate([
            'width' => ['numeric', 'min:0', 'max:2147483648'],
        ]);

        $request->validate([
            'discount' => ['numeric', 'min:1', 'max:99'],
        ]);

        // Если нет баркода то его генерируем
        $barcode = $request->barcode;

        //Если нет offer_id вставляем его из barcode
        $offer_id = $request->offer_id;

        $request->validate([
            'characteristics' => ['required', 'array'],
            'characteristics.*.id' => ['required', 'integer'],
        ]);


        $characteristics = $request->characteristics;

        foreach ($characteristics as $key => $characteristic) {
            $characteristicModel = Feature::query()->findOrFail($characteristic['id']);
            $type = $characteristicModel->type;
            //валидация в зависимости от типа поля UC6 2 релиз

            if (!$characteristicModel->is_reference) {
                switch ($type) {
                    case 'String':
                        $this->validate($request, [
                            "characteristics.{$key}.value" => ['nullable', 'string', 'max:255'],
                        ], [
                            'max' => 'Максимальная длина текста 255 символов.',
                        ], ["characteristics.{$key}.value" => $characteristic['value']]);
                        break;
                    case 'Decimal':
                        $this->validate($request, [
                            "characteristics.{$key}.value" => ['nullable', 'numeric'],
                        ], [
                            'numeric' => 'Введено недопустимое значение, Введите число.',
                        ], ["characteristics.{$key}.value" => $characteristic['value']]);
                        break;
                    case 'URL':
                        $this->validate($request, [
                            "characteristics.{$key}.value" => [
                                'nullable',
                                function ($attribute, $value, $fail) {
                                    //Проверка на URL
                                    $regExpUrl = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/';
                                    //Регулярка для разделения урлов по пробелу, новой строке или запятой
                                    $urls = preg_split('/(?:,| |\n)+/', $value);
                                    $errors = [];
                                    foreach ($urls as $url) {
                                        if (!preg_match($regExpUrl, $url)) {
                                            $errors[] = "Неправильный формат ссылки $url \n";
                                        }
                                    }
                                    if ($errors) {
                                        $fail($errors);
                                    }
                                },
                            ],
                        ]);
                        break;
                    case 'Integer':
                        $this->validate($request, [
                            "characteristics.{$key}.value" => ['nullable', 'integer'],
                        ], [
                            'integer' => 'Введено недопустимое значение, Введите целое число.',
                        ], ["characteristics.{$key}.value" => $characteristic['value']]);
                        break;
                    case 'ImageURL':
                        $this->validate($request, [
                            "characteristics.{$key}.value" => [
                                'nullable',
                                'regex:/.*\.(jpg|jpeg)$/',
                                function ($attribute, $value, $fail) {
                                    //Проверка на URL
                                    $regExpUrl = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/';
                                    //Проверка на картинку
                                    $regExpImg = '/.*\.(jpg|jpeg)$/';
                                    //Регулярка для разделения урлов по пробелу, новой строке или запятой
                                    $urls = preg_split('/(?:,| |\n)+/', $value);
                                    $errors = [];
                                    foreach ($urls as $url) {
                                        if (!preg_match($regExpUrl, $url)) {
                                            $errors[] = "Неправильный формат ссылки $url \n";
                                        }
                                        if (!preg_match($regExpImg, $url)) {
                                            $errors[] = "Ссылка не ведет на изображение $url \n";
                                        }
                                    }
                                    if ($errors) {
                                        $fail($errors);
                                    }
                                },
                            ],
                        ]);
                        break;
                }
            }
        }

        $requiredCharacteristics = $category->features
            ->where('is_required', true)
            ->whereNotIn('id', OzonFeaturesConstants::NOT_FOR_SHOW_ON_DETAIL)
            ->pluck('name', 'id')
            ->toArray();

        $product = new OzProduct(
            [
                'user_id' => UserService::getUserid(),
                'account_id' => UserService::getAccountId(),
                'barcode' => $barcode,
                'offer_id' => $offer_id,
                'name' => $request->name,
                'category_id' => $categoryId,
                'price' => $request->price,
                'min_ozon_price' => $request->min_ozon_price,
                'vat' => $request->vat,
                'volume_weight' => 0,
                'marketing_price' => $request->marketing_price,
                'buybox_price' => $request->buybox_price,
                'premium_price' => $request->premium_price,
                'depth' => $request->depth,
                'height' => $request->height,
                'width' => $request->width,
                'weight' => $request->weight,
                'weight_unit' => $request->weight_unit,
                'dimension_unit' => $request->dimension_unit,
                'count_photos' => count($request->images) ?? 0,
                'rating' => 0,
                'count_reviews' => 0,
                'photo_url' => $request->images[0] ?? null,
                'images' => $request->images ?? [],
                'recomended_price' => $request->recomended_price ?? 0.0,
                'status_id' => 4,
                'is_for_sale' => 1,
                'old_price' => $request->old_price ?? 0.0,
            ]
        );

        $product->save();

        // Теперь записываем опции

        foreach ($characteristics as $key => $characteristic) {
            $characteristicModel = Feature::query()->findOrFail($characteristic['id']);
            if ($characteristicModel->is_reference) {
                if (is_array($characteristic['value'])) {
                    foreach ($characteristic['value'] as $optionId) {
                        $value = optional(OzFeatureOption::find($optionId))->value;
                        if ($value) {
                            (new OzProductFeature([
                                'product_id' => (int)$product->id,
                                'feature_id' => (int)$key,
                                'option_id' => (int)$optionId,
                                'value' => $value ?? '',
                            ]))->save();
                        }
                    }
                } else {
                    $value = optional(OzFeatureOption::find($characteristic['value']))->value;
                    if ($value) {
                        (new OzProductFeature([
                            'product_id' => (int)$product->id,
                            'feature_id' => (int)$key,
                            'option_id' => (int)$characteristic['value'],
                            'value' => $value ?? '',
                        ]))->save();
                    }
                }
            } else {
                if (is_array($characteristic['value'])) {
                    foreach ($characteristic['value'] as $value) {
                        if ($value) {
                            (new OzProductFeature([
                                'product_id' => (int)$product->id,
                                'feature_id' => (int)$key,
                                'value' => $value ?? '',
                            ]))->save();
                        }
                    }
                } else {
                    if ($characteristic['value']) {
                        (new OzProductFeature([
                            'product_id' => (int)$product->id,
                            'feature_id' => (int)$key,
                            'value' => $characteristic['value'] ?? '',
                        ]))->save();
                    }
                }
            }

        }
        dispatch(new UpdateOzOptimisation($product));
        $response = (new ProductServiceCreatorImporter($product->id))->send();

        return response()->api_success(['result' => $response], 200);
    }

    /**
     * Отправка продукта в Ozon (через само API если продукт новый и у него нет никакой истории)
     */
    public function sendProduct(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $response = (new ProductServiceCreatorImporter($id))->send();

        return response()->api_success(['result' => $response], 200);
    }

    /**
     * Создание тестового продукта с правильными параметрами
     *
     * @param Request $request
     */
    public function createProductTest(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $account = UserService::getCurrentAccount();

        if (is_array($account)) {
            return $account;
        }

        $ozon = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $attributes = [
            [
                'complex_id' => 0,
                'id' => 85,
                'values' => [
                    ['dictionary_value_id' => 426723897],
                ],
            ],
            [
                'complex_id' => 0,
                'id' => 4194,
                'values' => [
                    ['value' => 'https://images.wbstatic.net/c516x688/new/4510000/4510832-1.jpg'],
                ],
            ],
            [
                'complex_id' => 0,
                'id' => 8229,
                'values' => [
                    ['dictionary_value_id' => 93437],
                ],
            ],
            [
                'complex_id' => 0,
                'id' => 10289,
                'values' => [
                    ['value' => 'аксессуар'],
                ],
            ],
            [
                'complex_id' => 0,
                'id' => 4191,
                'values' => [
                    [
                        'value' => 'Мелкая текстура, отличная сцепка с био клеем. Индивидуальная упаковка баночка с завинчивающейся крышкой для многоразового использования. Удобное хранение. Срок годности не ограничен. Возрастное ограничение по использованию',
                    ],
                ],
            ],
        ];
        $r = [
            'attributes' => $attributes,
            'barcode' => '2016278876887',
            'category_id' => 53261977,
            'color_image' => 'желтый',
            'depth' => 80,
            'dimension_unit' => 'mm',
            'height' => 80,
            'images' => ['https://images.wbstatic.net/c516x688/new/4510000/4510832-1.jpg'],
            'name' => 'Блестки резаные Желтые, 150 г',
            'offer_id' => '801-20501012test',
            'old_price' => '160',
            'price' => '160',
            'vat' => '0.2',
            'weight' => 160,
            'weight_unit' => 'g',
            'width' => 80,
        ];

        $res = $ozon->createProduct($r);

        dd($res);


    }

    /**
     * Генерация и скачивание рекомендаций в pdf файле
     *
     * @param Request $request
     * @return mixed
     */
    public function downloadPdfRecomendation(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var Product $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        return PDF::loadView('pdf.recomendations', compact('product'))->stream("product_{$id}.pdf");
    }

    /**
     * Сброс всех триггеров
     *
     * @param Request $request
     * @return mixed
     */
    public function clearAllTriggers(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->clearAllTriggers();

        return response()->api_success([], 203);
    }

    /**
     * Сброс триггеров по изменению минимального количества фото в ТОП-36
     *
     * @param Request $request
     * @return mixed
     */
    public function clearPhotoTriggers(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->clearPhotoTriggers();

        return response()->api_success([], 203);
    }

    /**
     * Сброс триггеров по изменению минимального количества отзывов в ТОП-36
     *
     * @param Request $request
     * @return mixed
     */
    public function clearReviewTriggers(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->clearReviewTriggers();

        return response()->api_success([], 203);
    }

    /**
     * Сброс триггеров по падению позиции карточки
     *
     * @param Request $request
     * @return mixed
     */
    public function clearPositionTriggers(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->clearPositionTriggers();

        return response()->api_success([], 203);
    }

    /**
     * Сброс триггеров по изменению количества характеристик
     *
     * @param Request $request
     * @return mixed
     */
    public function clearFeatureTriggers(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->clearFeatureTriggers();

        return response()->api_success([], 203);
    }

    /**
     * Сброс триггеров по снятию товара с продажи
     *
     * @param Request $request
     * @return mixed
     */
    public function clearRemoveFromSaleTriggers(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $user = $request->user();
        /**
         * @var OzProduct $product
         */
        $product = $user->products->find($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->clearRemoveFromSaleTriggers();

        return response()->api_success([], 203);
    }

    /**
     * Сбросить флаги обновления карточки.
     *
     * @param Request $request
     * @param         $id
     * @return mixed
     */
    public function resetUpdatedFlags(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $user = $request->user();
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $product->resetUpdateFlags();

        return response()->api_success([], 200);
    }

    /**
     * Сбросить флаг алерта
     *
     * @param Request $request
     * @param         $id
     * @return mixed
     */
    public function resetShowSuccessAlertFlag(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }
        $product->show_success_alert = false;
        $product->save();

        return response()->api_success([], 200);
    }

    /**
     * Получить список тестовых продуктов пользователя
     * @return JsonResponse
     */
    public function getTestProductsCount(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $userTestProducts = Product::currentUser()->where('is_test', true);
        $userTemporaryProducts = OzTemporaryProduct::currentUser();

        $availableTestProducts = 3 - $userTestProducts->count() - $userTemporaryProducts->count();

        return response()->api_success(
            [
                'available_test_products_to_create' => $availableTestProducts > 0 ? $availableTestProducts : 0,
            ],
            203
        );
    }

    /**
     * Установить успешный статус для товара после того
     * как пользователь ознакомился с ошибками
     * @param Request $request
     * @return mixed
     */
    public function setVerifiedStatus(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        /**
         * @var OzProduct $product
         */
        $product = OzProduct::findWithCurrentUser($request->product_id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        $successStatus = OzProductStatus::query()
            ->where('code', ProductStatusesConstants::VERIFIED_CODE)
            ->first();

        $product->status_id = $successStatus->id;
        $product->save();

        return response()->api_success([], 200);
    }


    /**
     * Поиск брендов у "скрытых" продуктов
     * @param Request $request
     * @return mixed
     */
    public function selectNotActiveBrands(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $query = OzTemporaryProduct::select('brand', DB::raw("COUNT(brand) AS qty"));
        if ($request->search) {
            $query = $query->where('brand', 'LIKE', "%{$request->search}%");

            return $query->currentAccount()->orderBy('brand', 'ASC')
                ->groupBy('brand')->paginate(999)->setPath('');
        } else {
            return $query->currentAccount()->orderBy('brand', 'ASC')
                ->groupBy('brand')->paginate()->setPath('');
        }

    }

    /**
     * Get analytics data
     * todo remove to repository class
     * @param Request $request
     * @return mixed
     */
    public function getAnalyticsData(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $productId = $request->get('product_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $product = OzProduct::where('id', $productId)->first();
        if (!$product) {
            return response()->api_fail('Такого продукта нет', [], 422);
        }
        $data = $product->positionAnalytics()->orderBy('report_date');
        if (isset($startDate)) {
            $data->where('report_date', '>', $startDate);
        }
        if (isset($endDate)) {
            $data->where('report_date', '<', $endDate);
        }
        $data = $data->select('hits_view_pdp', 'revenue', 'ordered_units', 'report_date')
            ->limit(30)
            ->get();
        $hits_view_pdp = [];
        $revenue = [];
        $ordered_units = [];
        foreach ($data as $row) {
            $hits_view_pdp = array_merge($hits_view_pdp, [$row->report_date => $row->hits_view_pdp]);
            $revenue = array_merge($revenue, [$row->report_date => $row->revenue]);
            $ordered_units = array_merge($ordered_units, [$row->report_date => $row->ordered_units]);
        }
        $data = [];
        $data[] = ['title' => 'Просмотры', 'graph' => $hits_view_pdp];
        $data[] = ['title' => 'Выручка', 'graph' => $revenue];
        $data[] = ['title' => 'Продажи', 'graph' => $ordered_units];
        return response()->api_success([$data], 200);
    }

    /**
     * Get position history
     * todo remove to repository class
     * @param Request $request
     * @return mixed
     */
    public function getPositionsHistory(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $productId = $request->get('product_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $product = OzProduct::where('id', $productId)->first();
        if (!$product) {
            return response()->api_fail('Такого продукта нет', [], 422);
        }
        $product = $product->positionHistory()
            ->select('category', 'date', 'position')
            ->orderBy('date');
        if (isset($startDate)) {
            $product->where('date', '>', $startDate);
        }
        if (isset($endDate)) {
            $product->where('date', '<', $endDate);
        }
        $positions = $product->limit(30)
            ->get();
        return response()->api_success([$positions], 200);
    }

    /**
     * Delete products from list
     * @throws Exception
     */
    private static function checkAndDeactivateProductQty()
    {
        $activeProductCount = OzProduct::currentUserAndAccount()->count();
        $max = UserService::getMaxProductsCount();
        if ($activeProductCount > $max) {
            $productsForDeactivate = $activeProductCount - $max;
            $externalIds = OzProduct::select(sprintf("%s.external_id", (new OzProduct)->getTable()))->currentUserAndAccount()
                ->latest()->limit($productsForDeactivate)->pluck('external_id')->toArray();
            $query = OzProduct::whereIn(sprintf("%s.external_id", (new OzProduct)->getTable()), $externalIds)->currentUserAndAccount();
            $query->delete();
            OzTemporaryProduct::whereIn('external_id', $externalIds)->currentAccount()->withTrashed()->restore();
        }
    }
}

