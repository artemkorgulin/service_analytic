<?php

namespace App\Services\Ozon;

use App\Models\OzProduct;
use App\Models\OzProductFeature;
use App\Repositories\Ozon\OzonProductFeatureRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OzonProductFeatureUpdateService
{

    /**
     * @param OzProductFeature $featureValue
     * @param OzonProductFeatureRepository $featureRepository
     */
    public function __construct(
        private OzProductFeature $featureValue,
        private OzonProductFeatureRepository $featureRepository
    ) {
        //
    }

    /**
     * @param array $features
     * @param int $productId
     * @return void
     */
    public function updateMassFeatureProductFromArray(array $features, int $productId)
    {
        foreach ($features as $feature) {
            $this->updateFromArray($feature, $productId);
        }
    }

    /**
     * @param array $feature
     * @param int $productId
     * @return bool
     */
    public function updateFromArray(array $feature, int $productId)
    {
        if (isset($feature['value'])) {

            $featureModel = $this->featureValue::where('feature_id', $feature['id'])
                ->where('product_id', $productId)
                ->first();

            if (!$featureModel) {
                return false;
            }

            $featureModel->value = $feature['value'];
            $featureModel->save();

            return true;
        }

        if (isset($feature['selected_options'])) {
            return $this->updateFeatureSelectListOptions($feature, $productId);
        }

        return false;
    }

    /**
     * @param array $feature
     * @param int $productId
     * @return bool
     */
    public function updateFeatureSelectListOptions(array $feature, int $productId)
    {
        // Clear all feature select/multiselect value
        DB::table('oz_products_features')
            ->where('product_id', $productId)
            ->where('feature_id', $feature['id'])
            ->delete();


        foreach ($feature['selected_options'] as $option) {

            $optionValue = $this->featureRepository->getCachedOptionValuesById($option);

            if (!$optionValue) {
                continue;
            }

            $this->featureValue::create([
                'product_id' => $productId,
                'feature_id' => $feature['id'],
                'value' => $optionValue->value,
                'option_id' => $option,
            ]);
        }

        return true;
    }

    /**
     * @param array $productArray
     * @param $features
     * @return void
     */
    public function getStaticFeatureByProductArray(array $productArray, &$features): void
    {
        // Add descriptions
        if (isset($productArray['descriptions'])) {
            $features[] = [
                'id' => OzProduct::PRODUCT_DESCRIPTION_FEATURE_ID,
                'value' => $productArray['descriptions'],
            ];
        }
        // Add youtubecodes
        if (isset($productArray['youtubecodes'])) {
            $features[] = [
                'id' => OzProduct::PRODUCT_YOUTUBE_FEATURE_ID,
                'value' => $productArray['youtubecodes'],
            ];
        } else {
            $features[] = [
                'id' => OzProduct::PRODUCT_YOUTUBE_FEATURE_ID,
                'value' => '',
            ];
        }
    }

    /**
     * @param $features
     * @return void
     */
    public function checkCompareCardFeature(&$features)
    {
        // Ищем в товаре характеристике "Объединить на одной карточке" и если не заполнена отправляем рандомную строку
        foreach ($features as $key => $characteristic) {
            if (in_array($characteristic['id'],
                    OzProduct::JOIN_CARD_CHARACRESTICS_IDS) && empty($characteristic->value)) {
                $features[$key]['value'] = Str::random(6);
                break;
            }
        }
    }
}
