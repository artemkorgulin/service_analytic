<?php

namespace App\Services\V2;

use App\Constants\OzonFeaturesConstants;
use App\Models\Feature;
use App\Models\Option;
use App\Models\OzProduct;
use App\Models\OzProductFeature;
use App\Services\InnerService;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Database\Eloquent\Collection;

/**
 * Класс для актуализации информации о характеристиках товаров
 * Class ProductsFeatureServiceUpdater
 * @package App\Services\V2
 */
class ProductsFeatureServiceUpdater
{
    /**
     * Коллекция товаров
     * @var Collection
     */
    protected $collection;

    /**
     * ProductsFeatureServiceUpdater constructor.
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Метод для обновления характеристик товара
     * @return void
     * @throws Exception|\App\Exceptions\Ozon\OzonServerException
     */
    public function updateFeatures(): void
    {
        $account_id = $this->collection->first()->account_id;
        $innerService = new InnerService();
        $account = $innerService->getAccount($account_id);
        if (is_bool($account)) {
            return;
        }
        $offers = $this->collection->pluck('offer_id')->all();
        $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $productInfoResponse = $ozonApiClient->ozonRepeat('getProductFeatures', $offers);

        if (!isset($productInfoResponse['data']['result'])) {
            throw new \Exception('Сервер Ozon api не вернул характеристик для продуктов с offer_id: ' .
                print_r($offers, true)
                . ' для аккаунта: ' . print_r($account, true)
                . ', ошибка: ' . json_encode($productInfoResponse));
        }

        $productsFeatures = $productInfoResponse['data']['result'];

        foreach ($productsFeatures as $productFeatures) {

            $getErrors = false;

            /** @var OzProduct $product */
            $product = $this->collection->filter(function ($item) use ($productFeatures) {
                return $item->offer_id === $productFeatures['offer_id'];
            })->first();

            //Перед тем как заполнить характеристики
            //удалим старые характеристики для этого товара
            OzProductFeature::query()
                ->where('product_id', $product->id)
                ->delete();

            foreach ($productFeatures["attributes"] as $productFeature) {
                /** @var Feature $feature */

                $feature = Feature::query()
                    ->where('id', $productFeature['attribute_id'])
                    ->whereHas('categories', function ($query) use ($product) {
                        $query->where('oz_categories.id', $product->category_id);
                    })
                    ->first();
                if ($feature) {
                    try {
                        $values = [];
                        foreach ($productFeature['values'] as $value) {
                            if ($value['dictionary_value_id']) {
                                if ($option = Option::query()
                                    ->where('id', $value['dictionary_value_id'])->first()) {
                                    $values[] = [
                                        'product_id' => $product->id,
                                        'feature_id' => $feature->id,
                                        'option_id' => $option->id,
                                        'value' => $option->value,
                                    ];
                                }
                            } else {
                                if (!empty($value['value'])) {
                                    $values[] = [
                                        'product_id' => $product->id,
                                        'feature_id' => $feature->id,
                                        'option_id' => null,
                                        'value' => $value['value'],
                                    ];
                                }
                            }
                        }
                        if ($values) {
                            OzProductFeature::insert($values);
                        }
                    } catch (\Exception $exception) {
                        // Если вылетело исключение, то характеристики не обновляем
                        report($exception);
                        ExceptionHandlerHelper::logFail($exception);
                        $getErrors = true;
                        $product->characteristics_updated = false;
                        $product->save();
                    }
                }

                if ($getErrors === false) {
                    $product->characteristics_updated = true;
                    $product->characteristics_updated_at = NOW();
                    $product->save();
                }
            }
        }
    }
}
