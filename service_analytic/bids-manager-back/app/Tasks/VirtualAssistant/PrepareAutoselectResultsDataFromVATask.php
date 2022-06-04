<?php

namespace App\Tasks\VirtualAssistant;

use App\Tasks\Task;

/**
 * Class prepareAutoselectResultsDataFromVATask
 *
 * @package App\Tasks\VirtualAssistant
 */
class PrepareAutoselectResultsDataFromVATask extends Task
{
    /**
     * @param array $serviceData
     * @return array
     */
    public function run(array $serviceData): array
    {
        $preparedData = [];
        $keywordGroupedData = [];
        foreach ($serviceData as $serviceDataRow) {
            $keywordGroupedData[$serviceDataRow->name][$serviceDataRow->category_id ? 'in_category' : 'no_category'][]
                = $serviceDataRow;
        }
        foreach ($keywordGroupedData as $key => $groupedData) {
            $name = '';
            if (isset($groupedData['no_category'])) {
                $name = $groupedData['no_category'][0]->name;
            } else if ($groupedData['in_category']) {
                $name = $groupedData['in_category'][0]->name;
            }
            $preparedData[$key] = [
                'name'                    => $name,
                'popularity'              => isset($groupedData['no_category']) ?
                    $this->sumColumnValue($groupedData['no_category'], 'popularity') : 0,
                'cart_add_count'          => isset($groupedData['no_category']) ?
                    $this->sumColumnValue($groupedData['no_category'], 'additions_to_cart') : 0,
                'avg_cost'                => isset($groupedData['no_category']) ?
                    $this->calculateAvgCost($groupedData['no_category']) : 0,
                'category_popularity'     => isset($groupedData['in_category']) ?
                    $this->sumColumnValue($groupedData['in_category'], 'popularity') : 0,
                'category_cart_add_count' => isset($groupedData['in_category']) ?
                    $this->sumColumnValue($groupedData['in_category'], 'additions_to_cart') : 0,
                'category_avg_cost'       => isset($groupedData['in_category']) ?
                    $this->calculateAvgCost($groupedData['in_category']) : 0,
            ];
        }
        return $preparedData;
    }


    /**
     * @param array  $arr
     * @param string $col
     * @return int
     */
    private function sumColumnValue(array $arr, string $col): int
    {
        return array_sum(array_column($arr, $col));
    }

    /**
     * @param array $arr
     * @return int
     */
    private function calculateAvgCost(array $arr): int
    {
        $costPerCartAdd = 0;
        foreach ($arr as $row) {
            $costPerCartAdd += $row->avg_price * $row->additions_to_cart;
        }
        return $costPerCartAdd;
    }
}
