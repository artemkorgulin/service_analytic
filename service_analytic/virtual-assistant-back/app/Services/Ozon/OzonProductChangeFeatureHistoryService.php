<?php

namespace App\Services\Ozon;

use App\Models\ProductFeatureHistory;

class OzonProductChangeFeatureHistoryService
{
    public function __construct(private ProductFeatureHistory $featureHistory)
    {
        //
    }

    /**
     * @param array $features
     * @param $productChangeHistoryId
     * @return void
     */
    public function createFeatureMassFromArray(array $features, $productChangeHistoryId)
    {
        foreach ($features as $feature) {
            // @TODO Save all select options in json field in at one row
            if (isset($feature['selected_options'])) {
                foreach ($feature['selected_options'] as $featureOption) {
                    $this->createFeatureHistoryChanges(
                        $productChangeHistoryId,
                        $feature['id'],
                        $featureOption,
                        true
                    );
                }
            }

            if (isset($feature['value'])) {
                $this->createFeatureHistoryChanges(
                    $productChangeHistoryId,
                    $feature['id'],
                    $feature['value'],
                    true
                );
            }
        }
    }

    /**
     * @param int $historyId
     * @param int $featureId
     * @param mixed $value
     * @param bool $isSend
     * @return mixed
     */
    public function createFeatureHistoryChanges(
        int $historyId,
        int $featureId,
        mixed $value,
        bool $isSend,
    ) {
        return $this->featureHistory::create([
            'history_id' => $historyId,
            'feature_id' => $featureId,
            'value' => $value,
            'is_send' => $isSend,
        ]);
    }
}
