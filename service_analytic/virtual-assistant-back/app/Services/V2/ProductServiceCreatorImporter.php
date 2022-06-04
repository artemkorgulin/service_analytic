<?php

namespace App\Services\V2;

use App\Models\Option;
use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Models\ProductChangeHistory;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Exceptions\EntityNotFoundException;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;

/**
 * Class ProductServiceImporter
 * Класс с методами для импорта товаров в Озон
 * @package App\Services\V2
 */
class ProductServiceCreatorImporter
{
    public const NAME_FEATURE_ID = 4180;


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
     * ProductServiceCreatorImporter
     * @param int $id - id истории изменений
     */
    public function __construct(int $id)
    {
        $this->productModel = OzProduct::find($id);
        if (empty($this->productModel)) {
            throw new EntityNotFoundException('Продукт не найден');
        }
        $account = UserService::getCurrentAccount();
        $this->ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
    }

    /**
     * Отправка данных в Ozon
     */
    public function send()
    {
        $data = $this->buildFromModel();
        $importResponse = (array)$this->ozonApiClient->createProduct($data);
        if (isset($importResponse['data']['result']['result']['task_id'])) {
            $taskId = $importResponse['data']['result']['result']['task_id'];
            return (new ProductChangeHistory([
                'product_id' => $this->productModel->id,
                'name' => $this->productModel->name,
                'status_id' => $this->productModel->status_id,
                'task_id' => $taskId,
                'is_send' => 1,
                'request_data' => $data,
                'response_data' => $importResponse,
            ]))->save();
        } elseif (isset($importResponse['data']['result']['task_id'])) {
            $taskId = $importResponse['data']['result']['task_id'];
            return (new ProductChangeHistory([
                'product_id' => $this->productModel->id,
                'name' => $this->productModel->name,
                'status_id' => $this->productModel->status_id,
                'task_id' => $taskId,
                'is_send' => 1,
                'request_data' => $data,
                'response_data' => $importResponse,
            ]))->save();
        } elseif (isset($importResponse['result']['task_id'])) {
            $taskId = $importResponse['result']['task_id'];
            return (new ProductChangeHistory([
                'product_id' => $this->productModel->id,
                'name' => $this->productModel->name,
                'status_id' => $this->productModel->status_id,
                'task_id' => $taskId,
                'is_send' => 1,
                'request_data' => $data,
                'response_data' => $importResponse,
            ]))->save();
        } else {
            try {
                (new ProductChangeHistory([
                    'product_id' => $this->productModel->id,
                    'name' => $this->productModel->name,
                    'status_id' => $this->productModel->status_id,
                    'task_id' => 0,
                    'is_send' => 1,
                    'request_data' => $data,
                    'response_data' => $importResponse,
                ]))->save();
            } catch (\Exception $exception) {
                ExceptionHandlerHelper::logFail($exception);
                return $exception->getMessage();
            }

        }

        return $importResponse;
    }


    /**
     * Строим массив для отправки в Ozon через API
     * @param $product
     */
    protected function buildFromModel()
    {
        $product = $this->productModel;

        $productFeatures = $product->featuresValues()->get();

        // получим изменения, которые внесли
        $attributes = [];

        foreach ($productFeatures as $productFeature) {
            $feature = $productFeature->feature;
            $featureExternalId = (int)$feature->id;
            if (!isset($attributes[$featureExternalId])) {
                $attributes[$featureExternalId] = [
                    'complex_id' => 0,
                    'id' => (int)$featureExternalId,
                    'values' => []
                ];
            }

            if ($productFeature->value) {
                if ($feature->is_reference) {
                    /** @var Option $option */
                    $option = $productFeature->option;
                    if (isset($option->id) && isset($option->value)) {
                        $attributes[$featureExternalId]['values'][] = [
                            'dictionary_value_id' => (int)$option->id,
                            'value' => $option->value
                        ];
                    }

                } else {
                    $attributes[$featureExternalId]['values'][] = [
                        'dictionary_value_id' => 0,
                        'value' => $productFeature->value
                    ];
                }
            }
        }

        $newAttributes = [];

        foreach ($attributes as $key => $attribute) {
            $newAttributes[] = $attribute;
        }

        $data['attributes'] = $newAttributes;

        $data['images'] = $product->images;
        $data['images360'] = $product->images360;
        $data['color_image'] = $product->color_image;
        $data['barcode'] = $product->barcode;
        $data['offer_id'] = $product->offer_id;
        $data['name'] = $product->name;
        $data['category_id'] = OzCategory::getExternalId($product->category_id);
        $data['vat'] = $product->vat;
        $data['price'] = (string)$product->price;
        if ($product->min_ozon_price)
            $data['min_ozon_price'] = (string)$product->min_ozon_price;
        if ($product->marketing_price)
            $data['marketing_price'] = (string)$product->marketing_price;
        if ($product->buybox_price)
            $data['buybox_price'] = (string)$product->buybox_price;
        if ($product->recommended_price)
            $data['recommended_price'] = (string)$product->recommended_price;
        $data['premium_price'] = $product->premium_price ?? "0";
        $data['dimension_unit'] = $product->dimension_unit;
        $data['depth'] = $product->depth;
        $data['height'] = $product->height;
        $data['width'] = $product->width;
        $data['weight_unit'] = $product->weight_unit;
        $data['weight'] = $product->weight;

        return $data;
    }


}
