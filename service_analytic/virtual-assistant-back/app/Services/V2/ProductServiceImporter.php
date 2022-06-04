<?php

namespace App\Services\V2;

use App\Jobs\OzonProductPriceChange;
use App\Models\Feature;
use App\Models\Option;
use App\Models\OzProduct;
use App\Models\ProductChangeHistory;
use App\Models\ProductFeatureHistory;
use App\Repositories\OzonProductRepository;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Exceptions\EntityNotFoundException;
use \App\DTO\Ozon\OzonProductSendDTO;

/**
 * Class ProductServiceImporter
 * Класс с методами для импорта товаров в Озон
 * @package App\Services\V2
 */
class ProductServiceImporter
{
    public const NAME_FEATURE_ID = 4180;

    /**
     * OZON product info
     * @var array
     */
    public array $productInfo;

    /**
     * Модель изменений товара
     * @var ProductChangeHistory
     */
    protected $productChangeHistoryModel;

    /**
     * Модель товара
     * @var OzProduct
     */
    protected $productModel;

    /**
     * @var OzonApi
     */
    protected $ozonApiClient;

    /**
     * ProductServiceImporter constructor.
     * @param int $id - id истории изменений
     */
    public function __construct(int $id)
    {
        $this->productChangeHistoryModel = ProductChangeHistory::find($id);

        if ($this->productChangeHistoryModel === null) {
            throw new EntityNotFoundException('История изменений не найдена');
        }

        $this->productModel = $this->productChangeHistoryModel->product()->first();
        $account = UserService::getCurrentAccount();
        $this->ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
    }

    /**
     * Метод для отправки данных в озон
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    public function send(): void
    {
        $this->productChangeHistoryModel->load('priceChanges');
        //собираем массив, который будет отправляться в озон
        $data = $this->buildArrayFromApi();
        $data = $this->buildArrayFromDB($data);

        $images = request()->images;
        if (!$images) {
            $images = [];
        }

        $data['images'] = array_filter($images);
        $data['primary_image'] = $data['images'][0] ?? '';
        $data['images360'] = request()->images360;
        $data['color_image'] = request()->colorSample;
        $data['price'] = number_format(request()->price, 2, '.', '');
        $data['premium_price'] = number_format(request()->premium_price, 2, '.', '');
        $data['old_price'] = number_format(request()->old_price, 2, '.', '');

        unset($data['prices']);
        unset($data['db_price']);
        unset($data['promocodes']);

        $importResponse = $this->ozonApiClient->ozonRepeat('importProduct', [$data]);

        $taskId = $importResponse['data']['result']['task_id'] ?? 1;
        $this->productChangeHistoryModel->task_id = $taskId;
        $this->productChangeHistoryModel->save();

        // если менялась цена, то отправим её в озон
        if ($this->productChangeHistoryModel->priceChanges->count()) {
            $importPriceData = [
                "offer_id" => (string)$data['offer_id'],
                "old_price" => (string)request()->old_price,
                "premium_price" => (string)request()->premium_price,
                "price" => (string)request()->price,
                "product_id" => (int)$data['id'],
            ];

            OzonProductPriceChange::dispatch(UserService::getCurrentAccountOzon(), $importPriceData)->delay(now()->addMinutes(4));
        }
    }

    public function sendFromDatabase(int $product_id)
    {
        $ozonProductRepository = new OzonProductRepository();
        $product = $ozonProductRepository->getProductDataToSendInOzon($product_id);

        $productDTO = new OzonProductSendDTO($product);

        $productDTO->prepareProduct();
        $productDTO->prepareProductFeature();

        $readyRequest = $productDTO->getRequestArray();
        $importResponse = $this->ozonApiClient->ozonRepeat('importProduct', [$readyRequest]);

        $this->productChangeHistoryModel->request_data = $readyRequest;
        $this->productChangeHistoryModel->response_data = $importResponse;
        $this->productChangeHistoryModel->task_id = $importResponse['data']['result']['task_id'];
        $this->productChangeHistoryModel->save();

        // @TODO check this code !
        // если менялась цена, то отправим её в озон
        if ($this->productChangeHistoryModel->priceChanges->count()) {
            $importPriceData = [
                "offer_id" => (string)$readyRequest['offer_id'],
                "old_price" => (string)$readyRequest['old_price'],
                "premium_price" => (string)$readyRequest['premium_price'],
                "price" => (string)$readyRequest['price'],
                "product_id" => (int)$readyRequest['id'],
            ];

            OzonProductPriceChange::dispatch(UserService::getCurrentAccountOzon(), $importPriceData)->delay(now()->addMinutes(4));
        }
    }

    /**
     * Метод для формирования массива с данными из API
     * @return array
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    protected function buildArrayFromApi(): array
    {
        // информация о товаре
        if (empty($this->productInfo)) {
            $productInfoResponse = $this->ozonApiClient->ozonRepeat(
                'getProductInfo',
                $this->productModel->sku_fbo
            );
            $productInfo = $productInfoResponse['data']['result'];
        } else {
            $productInfo = $this->productInfo;
        }

        // информация о характеристиках товара
        $productFeaturesResponse = $this->ozonApiClient->ozonRepeat(
            'getProductFeatures',
            [$this->productModel->offer_id]
        );
        $productsFeatures = reset($productFeaturesResponse['data']['result']);

        //attributes
        $productsFeaturesAttributes = [];
        foreach ($productsFeatures['attributes'] as $attributes) {
            $productsFeaturesAttributes[] = [
                'complex_id' => $attributes['complex_id'],
                'id' => $attributes['attribute_id'],
                'values' => $attributes['values'],
            ];
        }

        // complex_attributes
        $productsFeaturesComplexAttributes = [];
        foreach ($productsFeatures['complex_attributes'] as $complexKey => $complexAttributes) {
            $currentComplexAttributes = [];
            foreach ($complexAttributes['attributes'] as $complexAttribute) {
                $values = [];
                foreach ($complexAttribute['values'] as $value) {
                    $values[] = [
                        'dictionary_value_id' => $value['dictionary_value_id'],
                        'value' => $value['value'],
                    ];
                }

                $currentComplexAttributes[] = [
                    "id" => $complexAttribute['attribute_id'],
                    "complex_id" => $complexAttribute['complex_id'],
                    "values" => $values,
                ];
            }
            $productsFeaturesComplexAttributes[] = [
                "attributes" => $currentComplexAttributes
            ];
        }

        // todo check it
        // I think this change format for delivery
        // images360
        $productsFeaturesImages360 = [];
        foreach ($productsFeatures['images360'] as $images360) {
            $productsFeaturesImages360[] = $images360['file_name'];
        }

        // pdf_list
        $productsFeaturesPdfList = [];
        foreach ($productsFeatures['pdf_list'] as $pdfList) {
            $productsFeaturesPdfList[] = [
                "index" => $pdfList["index"],
                "name" => $pdfList["name"],
                "src_url" => $pdfList["file_name"],
            ];
        }

        return [
            'attributes' => $productsFeaturesAttributes,
            'barcode' => $productInfo['barcode'],
            'category_id' => $productInfo['category_id'],
            'complex_attributes' => $productsFeaturesComplexAttributes,
            'depth' => $productsFeatures['depth'],
            'dimension_unit' => $productsFeatures['dimension_unit'],
            'height' => $productsFeatures['height'],
            'width' => $productsFeatures['width'],
            'weight_unit' => $productsFeatures['weight_unit'],
            'weight' => $productsFeatures['weight'],
            'primary_image' => $productInfo['primary_image'],
            'image_group_id' => $productsFeatures['image_group_id'],
            'images' => $productInfo['images'] ?? $productsFeatures['images'] ?? [],
            'images360' => $productsFeaturesImages360,
            'offer_id' => $productInfo['offer_id'],
            'old_price' => $productInfo['old_price'],
            'pdf_list' => $productsFeaturesPdfList,
            'premium_price' => $productInfo['premium_price'],
            'price' => $productInfo['price'],
            'vat' => $productInfo['vat'],
            'id' => $productInfo['id']
        ];
    }

    /**
     * Метод для формирования массива с данными из БД
     * @param array $data
     * @return array
     */
    protected function buildArrayFromDB(array $data): array
    {
        $data['name'] = $this->productChangeHistoryModel->name;

        // Интересную штуку делают зачем то запрашивают дополнительно из API продукт
        $data['old_price'] = $data['db_price']['old_price'] = $this->productModel->old_price;

        if ($this->productChangeHistoryModel->priceChanges->count()) {
            $priceChange = $this->productChangeHistoryModel->priceChanges->sortByDesc('created_at')->first();
            // До того как цена обновится, сохраним её в дополнительное поле
            $data['db_price']['price'] = $priceChange->price ?? $data['price'];
            $data['db_price']['old_price'] = $priceChange->old_price ?? $data['old_price'];
        }

        $changedFeatures = $this->productChangeHistoryModel->changedFeatures()->get();
        /** @var ProductFeatureHistory $changedFeature */

        // получим изменения, которые внесли
        $dbFeatures = [];
        foreach ($changedFeatures as $changedFeature) {
            /** @var Feature $feature */
            $feature = $changedFeature->feature()->first();
            if (!isset($dbFeatures[$feature->id])) {
                $dbFeatures[$feature->id] = [
                    'complex_id' => 0,
                    'id' => $feature->id,
                    'values' => []
                ];
            }

            if ($changedFeature->value) {
                if ($feature->is_reference) {
                    /** @var Option $option */
                    $option = Option::query()
                        ->where('id', $changedFeature->value)->first();

                    if (isset($option->id) && isset($option->value)) {
                        $dbFeatures[$feature->id]['values'][] = [
                            'dictionary_value_id' => $option->id,
                            'value' => $option->value
                        ];
                    }

                } else {
                    $dbFeatures[$feature->id]['values'][] = [
                        'dictionary_value_id' => 0,
                        'value' => $changedFeature->value
                    ];
                }
            }
        }

        // заменим значения, которые уже установлены
        foreach ($data['attributes'] as &$attribute) {
            if ($attribute['id'] === self::NAME_FEATURE_ID) {
                $attribute['values'] = [
                    [
                        'dictionary_value_id' => 0,
                        'value' => $this->productChangeHistoryModel->name,
                    ]
                ];
            }

            if (isset($dbFeatures[$attribute['id']])) {
                $attribute = $dbFeatures[$attribute['id']];
                unset($dbFeatures[$attribute['id']]);
            }
        }
        unset($attribute);

        // добавим новые значения
        if (!empty($dbFeatures)) {
            foreach ($dbFeatures as $dbFeature) {
                $data['attributes'][] = $dbFeature;
            }
        }

        // Add here characteristics with dimensions and weight
        $data['weight_unit'] = $this->productModel->weight_unit ?? 'g';
        $data['weight'] = $this->productModel->weight ?? 0;
        $data['dimension_unit'] = $this->productModel->dimension_unit ?? 'mm';
        $data['depth'] = $this->productModel->depth ?? 0;
        $data['height'] = $this->productModel->height ?? 0;
        $data['width'] = $this->productModel->width ?? 0;

        return $data;
    }

    /**
     * Отправка изменений цен в Озон
     *
     * @param $data
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    protected function sendPrices($data)
    {
        $pricesImportResponse = $this->ozonApiClient->curlImportPrices($data);

        if (is_string($pricesImportResponse)) {
            $pricesImportResponse = json_decode($pricesImportResponse, true);
        }

        $pricesImportResponse = $pricesImportResponse['data']['result'][0] ?? $pricesImportResponse['result'][0];
        $priceChange = $this->productChangeHistoryModel->priceChanges->sortByDesc('created_at')->first();
        $priceChange->is_applied = $pricesImportResponse['updated'];
        //Если изменения применились, запишем их для импорта
        if ($priceChange->is_applied && isset($data['db_price'])) {
            $data['price'] = $data['db_price']['price'];
            $data['old_price'] = $data['db_price']['old_price'];
            unset($data['db_price']);
        }

        foreach ($pricesImportResponse['errors'] as $error) {
            $priceChange->errors .= $error['code'] . ' ' . $error['message'] . '/';
        }
        $priceChange->save();

        return $data;
    }
}
