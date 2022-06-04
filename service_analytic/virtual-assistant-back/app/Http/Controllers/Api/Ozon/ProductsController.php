<?php

namespace App\Http\Controllers\Api\Ozon;

use App\Constants\Errors\ProductsErrors;
use App\Exceptions\Product\ProductException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\CommonProductSegmentFilterRequest;
use App\Http\Requests\OzDeleteGoodsListRequest;
use App\Http\Requests\Ozon\OzonFeatureCategoryListRequest;
use App\Http\Requests\Ozon\OzonGetPlatformProductsPricesRequest;
use App\Http\Requests\Ozon\OzonGetProductListRequest;
use App\Http\Requests\Ozon\OzonProductMassUpdateRequest;
use App\Http\Requests\Ozon\OzonProductShowRequest;
use App\Http\Requests\OzUsingKeywordRequest;
use App\Http\Requests\OzWbSearchRequest;
use App\Jobs\CreateOzProductFromTemporary;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\OzLoadAnalyticsData;
use App\Jobs\UpdateOzOptimisation;
use App\Models\OzListGoodsAdd;
use App\Models\OzListGoodsUser;
use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;
use App\Models\PlatfomSemantic;
use App\Models\PlatformSemanticsRufago;
use App\Models\WebCategory;
use App\Repositories\Common\CommonProductDashboardRepository;
use App\Repositories\Ozon\OzonPlatformProductRepository;
use App\Repositories\Ozon\OzonProductFeatureRepository;
use App\Repositories\OzonProductRepository;
use App\Services\Ozon\OzonProductUpdateMassService;
use App\Services\UserService;
use App\Services\V2\ProductTrackingService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Classes\OzKeyRequestHandler;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public const COUNT_RESPONSE = 60;

    private function setRequest(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $account = UserService::getCurrentAccount();

        if (!is_array($account)) {
            return $account;
        }

        $this->user = $request->get('user');
        $this->page = abs((int) $request->page) ?: 1;
        if (app()->runningInConsole() === false && $this->user) {
            if ($request->perPage) {
                $this->pagination = (int) $request->perPage;
                Cache::put($this->user['id'].$this->paginationPostfix, $this->pagination);
            } else {
                if (Cache::has($this->user['id'].$this->paginationPostfix)) {
                    $this->pagination = Cache::get($this->user['id'].$this->paginationPostfix);
                }
            }
        }
    }

    /**
     * Get product semantics
     *
     * @param  OzonProductShowRequest  $request
     * @param  OzKeyRequestHandler  $ozKeyRequestHandler
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(
        OzonProductShowRequest $request,
        OzKeyRequestHandler $ozKeyRequestHandler,
        int $id
    ): JsonResponse {
        $this->setRequest($request, $ozKeyRequestHandler);
        $productSemantics = PlatfomSemantic::where('product_id', '=', $id)->first();
        return response()->api_success($productSemantics);
    }

    /**
     * Поиск брендов у "скрытых" продуктов
     * @param  Request  $request
     * @return mixed
     */
    public function selectNotActiveBrands(Request $request, OzKeyRequestHandler $ozKeyRequestHandler): mixed
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        return OzonProductRepository::getNotActiveBrands(
            UserService::forbiddenBrands() ?? [],
            $request->type ?? 'fbo',
            $request->availability ?? 0,
            $request->search ?? ''
        );
    }

    /**
     * Поиск "скрытых" продуктов
     * @param  Request  $request
     * @param  OzKeyRequestHandler  $ozKeyRequestHandler
     * @return mixed
     */
    public function selectNotActiveProducts(Request $request, OzKeyRequestHandler $ozKeyRequestHandler): mixed
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        return OzonProductRepository::getNotActiveProducts(
            UserService::forbiddenBrands() ?? [],
            $request->type ?? 'fbo',
            $request->availability ?? 0,
            $request->search ?? '',
            $request->brand ?? ''
        );
    }

    /**
     * Добавление предварительно загруженных товаров
     * @param  Request  $request
     * @param  OzKeyRequestHandler  $ozKeyRequestHandler
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function activateNotActiveProducts(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $request->validate(['ids' => ['required', 'array']]);

        $max = UserService::getMaxProductsCount();

        if (OzProduct::currentUserAndAccount()->count() > ($max + count($request->get('ids')))) {
            return response()->api_fail("Вы можете добавить максимально $max продукта(ов) на одну платформу!",
                [], 403);
        }

        try {
            $temporaryProducts = OzTemporaryProduct::whereIn('id', $request->ids)->currentAccount()->get();
            foreach ($temporaryProducts as $tmpProduct) {
                $ozDeletedProduct = OzProduct::where([
                    'offer_id' => $tmpProduct->offer_id,
                    'external_id' => $tmpProduct->external_id,
                ])->withTrashed()->first();

                // Product added by other user
                if (self::countProductActiveAndExist($tmpProduct->external_id) > 0
                    && $ozDeletedProduct->account_id === $tmpProduct->account_id) {
                    continue;
                }

                if ($ozDeletedProduct) {
                    $ozDeletedProduct->update([
                        'sku_fbo' => $tmpProduct->sku_fbo,
                        'sku_fbs' => $tmpProduct->sku_fbs,
                        'quantity_fbo' => $tmpProduct->quantity_fbo,
                        'quantity_fbs' => $tmpProduct->quantity_fbs,
                    ]);
                    $ozDeletedProduct->restore();
                    $ozDeletedProduct->attachUserAndAccount(UserService::getUserId(), UserService::getAccountId());
                    OzLoadAnalyticsData::dispatch($ozDeletedProduct);
                    UpdateOzOptimisation::dispatch($ozDeletedProduct)->onQueue('default_long');

                } else {
                    // Initially create and upload temporary product
                    $product = (new ProductTrackingService)->trackProduct($tmpProduct->sku_fbo);
                    $product->update([
                        'quantity_fbo' => $tmpProduct->quantity_fbo,
                        'quantity_fbs' => $tmpProduct->quantity_fbs,
                    ]);
                    $product->attachUserAndAccount(UserService::getUserId(), UserService::getAccountId());
                    CreateOzProductFromTemporary::dispatch($tmpProduct, $product)->onQueue('default_long');
                    UpdateOzOptimisation::dispatch($product)->onQueue('default_long');
                    DashboardAccountUpdateJob::dispatch(
                        UserService::getUserId(),
                        UserService::getAccountId(),
                        UserService::getCurrentAccountPlatformId()
                    )->delay(now()->addMinute(2));
                }
            }
        } catch (Exception $exception) {
            return response(json_encode([
                'error' => $exception->getMessage()
            ]), 400);
        }

        return response()->json([$request->ids]);
    }

    /**
     * Получение списка рекламных продуктов
     * @param  int  $id
     *
     */
    public function getListGoodsAdds(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, $id)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $listGoodsAdd = [];
        $countProduct = PlatfomSemantic::where('product_id', '=', $id)->count();
        if ($countProduct) {
            $listGoodsAdd = $ozKeyRequestHandler->getListGoods($id);
        }
        return $listGoodsAdd;
    }

    /**
     * Получение списка продуктов
     * @param  Request  $request
     * @return mixed
     */
    public function addGoodsList(OzUsingKeywordRequest $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        $productId = $request->input('product_id');
        $data = $request->input('data');

        try {
            // Очищаем временную таблицу
            if ($request->input('delete_old')) {
                $ozKeyRequestHandler->deleteListGoodsAdds($productId);
            }
            // Записываем данные во временную таблицу
            $ozKeyRequestHandler->createListGoodsAdds($data, $productId);
            return response()->api_success([]);

        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function getPickList(Request $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);

        $productId = $request->input('product_id');

        $ozPickList = OzListGoodsAdd::select('id', 'oz_product_id', 'key_request as name', 'popularity', 'created_at',
            'updated_at')->where('oz_product_id', $productId)->get();

        if ($ozPickList->isEmpty()) {
            $ozPickList = OzListGoodsUser::select('id', 'oz_product_id', 'key_request as name', 'popularity',
                'created_at', 'updated_at')->where('oz_product_id', $productId)->get();
        }

        return response()->api_success($ozPickList);
    }

    /**
     * @param  OzDeleteGoodsListRequest  $request
     * @return mixed
     */
    public function destroyGoodsList(OzDeleteGoodsListRequest $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        if ($request->input('id')) {
            OzListGoodsAdd::where('id', $request->input('id'))->delete();
        } else {
            OzListGoodsAdd::where('oz_product_id', $request->input('oz_product_id'))
                ->where('key_request', $request->input('name'))
                ->delete();
        }

        return response()->api_success([]);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function getKeyRequests(OzWbSearchRequest $request, OzKeyRequestHandler $ozKeyRequestHandler)
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        try {
            $platfomSemantics = PlatformSemanticsRufago::query()
                ->select('search_response as name', DB::raw('max(popularity) as popularity'))
                ->whereIn('wb_product_name', $request->get('products'))
                ->groupBy('name')
                ->orderByDesc('popularity')
                ->limit(self::COUNT_RESPONSE * count($request->get('products')));

            $result = $platfomSemantics->get();

            if (count($result->toArray()) === 1 && $result->toArray()[0]['name'] === "0") {
                $result = [];
            }

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }


    /**
     * Get data for detail recommendation (top 36 of products parsers from Rufago)
     *
     * @param  Request  $request
     * @param  OzKeyRequestHandler  $ozKeyRequestHandler
     * @param  int  $id
     * @return mixed
     */
    public function getDetailRecommendations(Request $request, OzKeyRequestHandler $ozKeyRequestHandler, int $id): mixed
    {
        $this->setRequest($request, $ozKeyRequestHandler);
        try {
            $product = OzonProductRepository::getProduct($id);
            if (empty($product)) {
                throw new ProductException(ProductsErrors::NOT_FOUND);
            }
            if (empty($product->real_category)) {
                throw new ProductException(ProductsErrors::CHARACTERISTIC_NOT_FOUND);
            }
            $externalIdCategory = optional(
                WebCategory::where('full_name', $product->real_category)->first()
            )->external_id;
            if ($externalIdCategory) {
                $lastMinData = OzonProductRepository::getProductTop36($externalIdCategory, 'min');
                $lastAvgData = OzonProductRepository::getProductTop36($externalIdCategory, 'avg');
            }
            if (!empty($lastMinData) && !empty($lastAvgData)) {
                $response = [
                    'photo' => $lastAvgData->photo_count ?? 0,
                    'comments' => $lastMinData->review_count ?? 0,
                    'price' => $lastAvgData->price ?? 0,
                    'rating' => $lastMinData->rating ?? 0
                ];
            } else {
                $response = [
                    'photo' => 0,
                    'comments' => 0,
                    'price' => 0,
                    'rating' => 0
                ];
            }
        } catch (Exception $exception) {
            return response()->api_fail($exception->getMessage(), [], 404);
        }
        return response()->api_success($response, 200);
    }

    /**
     * Check product active and exist by id
     */
    private static function countProductActiveAndExist($externalId)
    {
        return OzProduct::where('external_id', $externalId)->count();
    }

    /**
     * @param  OzonProductMassUpdateRequest  $massUpdateRequest
     * @return mixed
     */
    public function modifyProductMass(OzonProductMassUpdateRequest $massUpdateRequest)
    {
        $massUpdateService = \App::make(OzonProductUpdateMassService::class);

        $massUpdateService->massUpdateRunner(
            $massUpdateRequest
        );

        return response()->api_success(['message' => __('response.product.mass_update.success')]);
    }

    /**
     * @param  OzonGetProductListRequest  $getProductListRequest
     * @param  OzonProductRepository  $ozonProductRepository
     * @return mixed
     */
    public function getProductsListWithFeature(
        OzonGetProductListRequest $getProductListRequest,
        OzonProductRepository $ozonProductRepository
    ): mixed {
        $ozonProductRepository->setQueryFilter(
            UserService::getUserId(),
            UserService::getAccountId(),
            $getProductListRequest->get('query') ?? null,
        );
        $ozonProductRepository->setMassUpdateProductField();

        $productsQuery = $ozonProductRepository->getQueryBuilder();
        $products = PaginatorHelper::addPagination($getProductListRequest, $productsQuery);

        if (!$products->count()) {
            return response()->api_success(['message' => __('response.product.list_with_feature.error')]);
        }

        return response()->api_success($products);
    }

    /**
     * @TODO cut this method to feature controller
     * @param  OzonFeatureCategoryListRequest  $featureCategoryListRequest
     * @param  OzonProductFeatureRepository  $featureRepository
     * @return mixed
     */
    public function categoryFeatureList(
        OzonFeatureCategoryListRequest $featureCategoryListRequest,
        OzonProductFeatureRepository $featureRepository
    ) {
        $features = $featureRepository
            ->getFeaturesCollectionByCategoryId($featureCategoryListRequest['query']['category_id'])
            ->toArray();

        return response()->api_success($features);
    }

    public function getProductsBySegmentFilter(
        CommonProductSegmentFilterRequest $productSegmentFilterRequest,
        OzonProductRepository $productRepository,
        CommonProductDashboardRepository $dashboardRepository,
    ) {
        $query = $productSegmentFilterRequest->get('query');

        $dashboard = $dashboardRepository->getAccountDashboardByType(
            UserService::getUserId(),
            UserService::getAccountId(),
            UserService::getCurrentAccountPlatformId(),
            $query['dashboard_type']
        );

        if (!$dashboard) {
            return response()->api_success([]);
        }

        $products = $productRepository->getProductsQueryByDashboardSegmentation(
            $dashboard,
            $query['segment_type'],
            $productSegmentFilterRequest->get('sortBy'),
            $productSegmentFilterRequest->get('sortType'),
        );

        if (!$products) {
            return response()->api_success([]);
        }

        $appends = [
            'sortBy' => $productSegmentFilterRequest->get('sortBy'),
            'sortType' => $productSegmentFilterRequest->get('sortType'),
        ];

        $products = PaginatorHelper::addPagination($productSegmentFilterRequest, $products)
            ->setPath('')
            ->appends($appends);

        // @TODO - need refactoring
        $products->setCollection(
            $this->formatProducts($products->getCollection())
        );

        return response()->api_success($products);
    }

    /**
     * Format ozon products
     *
     * @param $products
     * @return Collection
     */
    public function formatProducts($products): Collection
    {
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
                'brand' => $product->brand,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'premium_price' => $product->premium_price,
                'quantity' => [
                    'fbo' => $product->quantity_fbo,
                    'fbs' => $product->quantity_fbs,
                ],
                'photo_url' => $product->photo_url ?? $product->images[0] ?? '',
                'product_ozon_url' => $product->url ?? "https://www.ozon.ru/context/detail/id/{$product->sku_fbo}",
                'rating' => $product->rating,
                'count_reviews' => $product->count_reviews,
                'optimization' => $product->optimization,
                'content_optimization' => $product->content_optimization,
                'search_optimization' => $product->search_optimization,
                'visibility_optimization' => $product->visibility_optimization,
                'characteristics_updated_at' => $product->characteristics_updated_at,
                'characteristics_updated' => $product->characteristics_updated,
                'position_updated' => $product->position_updated,
                'mpstat_updated_at' => $product->mpstat_updated_at,
                'category' => $product->category_id,
                'status_id' => $product->status_id,
                'card_updated' => $product->card_updated,
                'is_test' => $product->is_test,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return collect($productList);
    }

    /**
     * @param  OzonGetPlatformProductsPricesRequest  $getPriceRequest
     * @return mixed
     */
    public function getPlatformProductsPrice(OzonGetPlatformProductsPricesRequest $getPriceRequest)
    {
        $ozonPlatformRepository = new OzonPlatformProductRepository(
            UserService::getCurrentAccountPlatformClientId(),
            UserService::getCurrentAccountPlatformApiKey()
        );

        $query = $getPriceRequest->input('query');

        $productPrices = $ozonPlatformRepository->getProductsPriceByExternalIds($query['external_id']);

        if (!$productPrices) {
            return response()->api_fail([], [], Response::HTTP_NOT_FOUND);
        }

        return response()->api_success($productPrices);
    }
}
