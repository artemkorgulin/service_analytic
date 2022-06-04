<?php

namespace App\Services\V2;

use App\Classes\Helper;
use App\Constants\References\ProductStatusesConstants;
use App\DTO\Ozon\ProductChangeHistoryDTO;
use App\Exceptions\Ozon\OzonApiException;
use App\Jobs\UpdateOzOptimisation;
use App\Models\OzCategory;
use App\Models\OzListGoodsAdd;
use App\Models\OzListGoodsUser;
use App\Models\OzProduct;
use App\Models\OzProductStatus;
use App\Models\ProductPositionHistory;
use App\Models\ProductPositionHistoryGraph;
use App\Models\TriggerRemoveFromSale;
use App\Repositories\Ozon\OzonProductChangeHistoryRepository;
use App\Services\InnerService;
use App\Services\Ozon\OzonParsingService;
use App\Services\Ozon\OzonProductChangeFeatureHistoryService;
use App\Services\Ozon\OzonProductChangeHistoryService;
use App\Services\Ozon\OzonProductChangePriceHistoryService;
use App\Services\Ozon\OzonProductFeatureUpdateService;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Exceptions\EntityNotFoundException;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Класс для актуализации информации о товаре
 * @TODO Cut this class to Ozon folder. Rename this class to OzonProductUpdateService
 * Class ProductServiceUpdater
 * @package App\Services\V2
 */
class ProductServiceUpdater
{
    const PAGE_SIZE = 36;

    /**
     * Модель товара
     * @var OzProduct
     */
    protected $model;

    protected static $platform_client_id;

    protected static $platform_api_key;

    /**
     * ProductServiceUpdater constructor.
     * @TODO Set DI in constructor, cut current logic to init method.
     * @TODO Refactor this divine class.
     *
     * @param int|OzProduct $product - id товара или объект OzProduct
     * @param null $account_id
     * @throws Exception
     */
    public function __construct($product, $account_id = null)
    {
        if (is_int($product)) {
            $this->model = OzProduct::find($product);
        } elseif ($product instanceof OzProduct) {
            $this->model = $product;
        } else {
            throw new Exception("Wrong argument type: " . print_r($product, true));
        }

        if ($this->model === null) {
            throw new EntityNotFoundException('Product not found');
        }

        if ($account_id) {
            $response = (new InnerService)->getAccount($account_id);
            if (!$response) {
                throw new Exception("Wrong account_id = {$account_id} for product with external_id = {$this->model->external_id}", 404);
            }
            self::$platform_client_id = $response['platform_client_id'];
            self::$platform_api_key = $response['platform_api_key'];
        }

    }

    /**
     * Метод для обновления информации из озона
     */
    public function updateInfo()
    {
        if (!empty($this->model->account_id) && !empty(self::$platform_client_id) && !empty(self::$platform_api_key)) {
            $this->model->card_updated = false;
            $this->model->save();

            $ozonApiClient = new OzonApi(self::$platform_client_id, self::$platform_api_key);
            $productInfoResponse = $ozonApiClient->ozonRepeat('getProductInfo', $this->model->sku_fbo);

            $productInfo = $productInfoResponse['data']['result'];
            /** @var OzCategory $category */
            $category = OzCategory::query()->where('external_id',
                $productInfo['category_id'])->first(); //todo что-то сделать, если категории нет
            $this->model->category_id = $category->id;
            $this->model->barcode = empty($productInfo['barcode']) ? '' : $productInfo['barcode'];
            $this->model->price = empty($productInfo['price']) ? 0 : $productInfo['price'];
            $this->model->premium_price = empty($productInfo['premium_price']) ? 0 : $productInfo['premium_price'];
            $this->model->buybox_price = empty($productInfo['buybox_price']) ? 0 : $productInfo['buybox_price'];
            $this->model->marketing_price = empty($productInfo['marketing_price']) ? 0 : $productInfo['marketing_price'];
            $this->model->min_ozon_price = empty($productInfo['min_ozon_price']) ? 0 : $productInfo['min_ozon_price'];
            $this->model->vat = empty($productInfo['vat']) ? '' : $productInfo['vat'];
            $this->model->volume_weight = empty($productInfo['volume_weight']) ? null : $productInfo['volume_weight'];
            $this->model->old_price = empty($productInfo['old_price']) ? 0 : $productInfo['old_price'];
            $this->model->name = $productInfo['name'];
            $this->model->offer_id = $productInfo['offer_id'];
            $this->model->recommended_price = empty($productInfo['recommended_price']) ? 0 : $productInfo['recommended_price'];
            $this->model->card_updated = true;

            // Если сняли с продажи, добавим триггер
            if ($this->model->is_for_sale && !$productInfo['visible']) {
                $triggerRemoveFromSale = new TriggerRemoveFromSale();
                $triggerRemoveFromSale->product_id = $this->model->id;
                $triggerRemoveFromSale->save();
            }

            $this->model->is_for_sale = $productInfo['visible'];
            $this->model->card_updated = true;
            $this->model->save();
            (new OzonParsingService)->createCategoryForProduct($this->model);
        }
    }

    /**
     * Update service for weight and
     */
    public function updateWeightsAndDimensions(): ?bool
    {
        try {
            $account = (new InnerService)->getAccount($this->model->account_id);
            if (!$account) {
                return false;
            }
            $ozonApi = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
            $response = $ozonApi->repeat('getProductFeatures', [$this->model->offer_id]);
            if (isset($response['statusCode']) && $response['statusCode'] == 200) {
                $productData = $response['data']['result'][0];
                $this->model->dimension_unit = $productData['dimension_unit'] ?? 'mm';
                $this->model->weight_unit = $productData['weight_unit'] ?? 'g';
                $this->model->depth = $productData['depth'] ?? 0;
                $this->model->height = $productData['height'] ?? 0;
                $this->model->width = $productData['width'] ?? 0;
                $this->model->weight = $productData['weight'] ?? 0;
                $this->model->save();
            }
            return true;
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
            return false;
        }
    }

    /**
     * Метод для обновления характеристик товара на основе данных из озона
     */
    public function updateFeatures(): void
    {
        $collection = Collection::make([$this->model]);
        $productsFeatureServiceUpdate = new ProductsFeatureServiceUpdater($collection);
        $productsFeatureServiceUpdate->updateFeatures();
        $this->model->characteristics_updated = true;
        $this->model->characteristics_updated_at = NOW();
        $this->model->save();
    }


    /*
     * @TODO Cut this logic to DTO or mapper.
     * Преобразовывает массив озона в статусы, которые у нас есть в БД
     * метод необходим после изменения апи озона
     */
    public function arrayToStatus($productInfo): string
    {
        $productStatus = 'moderating';
        if ($productInfo['is_failed']) {
            $productStatus = 'error';
        } elseif (empty($productInfo['moderate_status'])) {
            $productStatus = 'moderating';
        } elseif ($productInfo['moderate_status'] == 'approved') {
            $productStatus = 'processed';
        } elseif ($productInfo['validation_state'] != 'success') {
            $productStatus = 'failed_validation';
        }
        return $productStatus;
    }

    /**
     * Метод для обновления статуса модерации
     * @param string $status - статус товара: processed, processing, moderating. Иные статусы считаются за ошибку
     */
    public function updateStatus(string $status): void
    {
        switch ($status) {
            case 'processed':
                $status = OzProductStatus::query()
                    ->where('code', ProductStatusesConstants::VERIFIED_CODE)
                    ->first();
                $this->model->status_id = $status->id;
                $this->model->show_success_alert = true;
                break;

            case 'processing':
            case 'moderating':
                $status = OzProductStatus::query()
                    ->where('code', ProductStatusesConstants::MODERATION_CODE)
                    ->first();
                $this->model->status_id = $status->id;
                break;

            case 'failed_validation':
                $status = OzProductStatus::query()
                    ->where('code', 'failed_validation')
                    ->first();
                $this->model->status_id = $status->id;
                break;

            default:
                $status = OzProductStatus::query()
                    ->where('code', ProductStatusesConstants::ERROR_CODE)
                    ->first();
                $this->model->status_id = $status->id;
                break;
        }
        $this->model->save();
    }

    /**
     * Получить название самой глубокой позиции
     * @param array $categories
     * @return false|mixed
     */
    private function getDeepestCategoryKey(array $categories)
    {
        $mapping = array_combine($categories, array_map('strlen', $categories));
        $mapping = array_keys($mapping, max($mapping));
        return reset($mapping);
    }

    /**
     * Метод для обновления позиции продукта
     * Добавляет новую запись в таблицу с историей позиции
     */
    public function updateStats(): void
    {
        $this->model->position_updated = false;
        $this->model->save();

        //Запрашиваем информацию за 30 дней
        $dateFrom = Carbon::now()->subDays(30)->toDate();
        $dateTo = Carbon::now()->subDay()->toDate();

        $statsResponse = (new MPStatsApi)->getProductStats($this->model->sku_fbo, $dateFrom, $dateTo);

        if ($statsResponse['statusCode'] === 200 && isset($statsResponse['data'])
            && !empty($statsResponse['data']['categories'])) {

            //Записывает историю для всех дочерних категорий
            $this->updatePositionGraph($dateFrom, $dateTo, $statsResponse['data']['categories']);

            //До того как обновить позицию в самой глубокой категории
            //запомним последнюю позицию
            if ($this->model->positionHistory->count()) {
                $lastPosition = $this->model->positionHistory->whereNotNull('position')->last();
            }

            //Записывает историю только для самой глубокой категории
            $this->updatePositionHistories($dateFrom, $dateTo, $statsResponse['data']['categories']);

            $this->model->position_updated = true;
            $this->model->mpstat_updated_at = NOW();
            $this->model->save();
        }

        //Возьмём последнюю позицию после обновления
        if ($this->model->positionHistory->count()) {
            $updatedLastPosition = $this->model->positionHistory()->whereNotNull('position')->latest()->first();
        }

        if (isset($lastPosition) && isset($updatedLastPosition)) {
            $this->setTrigger($updatedLastPosition->position, $lastPosition->position, $updatedLastPosition);
            $updatedLastPosition->save();
        }
    }

    /**
     * Метод для обновления позиции продукта
     * Делает повторный запрос для необработанного продукта
     * @param ProductPositionHistory $unhandledProductPosition
     * @throws Exception
     */
    public function updateUnhandled(ProductPositionHistory $unhandledProductPosition): void
    {
        $this->model->position_updated = false;
        $this->model->save();

        $date = new DateTime($unhandledProductPosition->date);

        $statsResponse = (new MPStatsApi())->getProductStats($this->model->sku_fbo, $date, $date);

        $position = $categoryName = null;
        if ($statsResponse['statusCode'] === 200 && isset($statsResponse['data'])
            && !empty($statsResponse['data']['categories'])) {

            $categoryName = $this->getDeepestCategoryKey(array_keys($statsResponse['data']['categories']));
            $lastCategoryStats = $statsResponse['data']['categories'][$categoryName];
            $position = end($lastCategoryStats);

            $this->model->position_updated = true;
            $this->model->mpstat_updated_at = NOW();
            $this->model->save();
        }

        $unhandledProductPosition->date = Carbon::parse($date);
        $unhandledProductPosition->position = $position;
        $unhandledProductPosition->product_id = $this->model->id;
        $unhandledProductPosition->category = $categoryName;
        $unhandledProductPosition->save();
    }

    /**
     * Установка триггера
     * @param $position
     * @param $lastPosition
     * @param ProductPositionHistory $productPositionHistory
     */
    private function setTrigger($position, $lastPosition, ProductPositionHistory $productPositionHistory): void
    {
        if (
            $position
            && $lastPosition
            && $lastPosition <= self::PAGE_SIZE
            && $position - $lastPosition > 20
        ) {
            $productPositionHistory->is_trigger = true;
        } elseif (
            $position
            && $lastPosition
            && $lastPosition > self::PAGE_SIZE
            && intdiv($position, self::PAGE_SIZE) - intdiv($lastPosition, self::PAGE_SIZE) > 0
        ) {
            $productPositionHistory->is_trigger = true;
        }
    }

    /**
     * Обновить позицию
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param $categories
     */
    private function updatePositionHistories(DateTime $dateFrom, DateTime $dateTo, $categories): void
    {
        //Не сохраняем историю для ранее добавленных позиций
        $dateFrom = new Carbon($dateFrom);
        $dateFrom->subDay()->setTime(0, 0);
        $dateFrom = $dateFrom->format('Y-m-d');
        $dateTo = $dateTo->format('Y-m-d');

        try {
            $existingPositions = ProductPositionHistory::query()
                ->where('date', '>=', $dateFrom)
                ->where('date', '<=', $dateTo)
                ->where('product_id', '=', $this->model->id)
                ->get();

            //Позицию в самой глубокой категории запоминаем отдельно
            $categoryName = $this->getDeepestCategoryKey(array_keys($categories));
            $lastCategoryStats = $categories[$categoryName];
            $date = new Carbon($dateFrom);
            $date->subDay();
            foreach ($lastCategoryStats as $position) {
                if ($position == "NaN") {
                    $position = null;
                }
                $existingPosition = $existingPositions
                    ->where('date', $dateFrom)
                    ->where('category', $categoryName)
                    ->where('position', $position);
                if ($existingPosition->isEmpty()) {
                    $productPositionHistory = new ProductPositionHistory();
                    $productPositionHistory->date = $date;
                    $productPositionHistory->position = $position;
                    $productPositionHistory->product_id = $this->model->id;
                    $productPositionHistory->category = $categoryName;
                    $productPositionHistory->save();
                }
                $date->addDay();
            }
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }
    }

    /**
     * Записывает историю позиций для всех вложенных категорий
     * Создаёт запись в таблице PositionHistoryGraph
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param $categories
     */
    private function updatePositionGraph(DateTime $dateFrom, DateTime $dateTo, $categories): void
    {
        // MPstat возвращает на день раньше дату. Запрашиваем с 3 числа, в ответе позиции со 2
        $date = new Carbon($dateFrom);
        $date->subDay();

        // Conversion date
        try {
            $date = $date->format('Y-m-d');
            $dateTo = $dateTo->format('Y-m-d');
            //Не сохраняем историю для ранее добавленных позиций
            $existingPositions = $this->model->positionHistoryGraph()
                ->whereDate('date', '>=', $date)
                ->whereDate('date', '<=', $dateTo)
                ->get();

            //Проходим по всем подкатегориям, чтобы получить значения для графика динамики
            foreach ($categories as $categoryName => $categoryData) {
                $date = new Carbon($dateFrom);
                $date->subDay();
                foreach ($categoryData as $position) {
                    $existingPosition = $existingPositions
                        ->where('date', '=', $date->format('Y-m-d'))
                        ->where('category', '=', $categoryName);

                    if ($position != "NaN" && $existingPosition->isEmpty()) {
                        $positionHistoryGraph = new ProductPositionHistoryGraph();
                        $positionHistoryGraph->date = $date->toDate()->format('Y-m-d');
                        $positionHistoryGraph->position = $position;
                        $positionHistoryGraph->product_id = $this->model->id;
                        $positionHistoryGraph->category = $categoryName;
                        $positionHistoryGraph->save();
                    }
                    $date->addDay();
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }
    }


    /**
     * @TODO Adaptive for nomenclature update
     *
     * @param array $productArray
     * @return mixed
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    public function updateFromArray(array $productArray)
    {
        try {
            ModelHelper::transaction(function () use ($productArray) {
                $product = $this->model;
                $product->fill($productArray);
                $product->save();

                // Create product change history
                $historyDto = new ProductChangeHistoryDTO(
                    $product->id,
                    $product->name,
                    OzonProductChangeHistoryRepository::PRODUCT_CHANGE_HISTORY_STATUS_MODERATION_ID,
                    true,
                    null,
                    null,
                    null,
                );

                $productHistoryService = \App::make(OzonProductChangeHistoryService::class);
                $productHistory = $productHistoryService->createHistoryProductFromDTO($historyDto);

                if ($productArray['characteristics']) {
                    // Update features
                    $features = $productArray['characteristics'];
                    $featureService = \App::make(OzonProductFeatureUpdateService::class);
                    $featureService->getStaticFeatureByProductArray($productArray, $features);
                    $featureService->checkCompareCardFeature($features);
                    $featureService->updateMassFeatureProductFromArray($features, $productArray['id']);

                    $featureHistoryService = \App::make(OzonProductChangeFeatureHistoryService::class);
                    $featureHistoryService->createFeatureMassFromArray($features, $productHistory->id);
                }

                // Create product change price history
                if ($productArray['price'] || $productArray['old_price']) {
                    $priceChangeHistoryService = \App::make(OzonProductChangePriceHistoryService::class);
                    $priceChangeHistoryService->createPriceHistoryChanges(
                        $productArray['id'],
                        $productHistory->id,
                        $productArray['price'],
                        $productArray['old_price'] ?? $product->price,
                    );
                }

                //актуализируем список в таблице OzListGoodsUser
                $ozPickList = OzListGoodsAdd::select('oz_product_id', 'key_request', 'popularity')
                    ->where('oz_product_id', $productArray['id'])
                    ->get();

                $ozUsingKeywordToSave = $ozPickList->toArray();
                $ozUsingKeywordToSave = array_map(function ($element) {
                    $element['created_at'] = now();
                    return $element;
                }, $ozUsingKeywordToSave);

                OzListGoodsUser::where('oz_product_id', $productArray['id'])->delete();
                OzListGoodsUser::insert($ozUsingKeywordToSave);
                OzListGoodsAdd::where('oz_product_id', $productArray['id'])->delete();

                //отправляем изменения в озон
                $importer = new ProductServiceImporter($productHistory->id);
                $importer->sendFromDatabase($productArray['id']);

                $product->status_id = OzProductStatus::where('code',
                    ProductStatusesConstants::MODERATION_CODE)->first()->id;

                // TODO Cut this to job
                // Обновление продукта
                $this->updateStats();

                $product->brand = Helper::ozCardGetBrandFromCharacteristics($product->characteristics);
                $product->save();

                UpdateOzOptimisation::dispatch($product)->afterCommit();
            });
        } catch (Exception $e) {
            throw $e;
        }

        return response()->api_success([], 200);
    }
}
