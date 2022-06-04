<?php

namespace App\Services\Wildberries;

use App\Helpers\WbProductHelper;
use App\Models\RequiredCollectionWbCharacteristics;
use App\Models\WbDirectoryItem;
use App\Models\WbFeature;
use Illuminate\Support\Facades\DB;

class WbSearchService
{
    const useOnlyDictionaryValues = true;


    /**
     * Search characteristics for type and category
     *
     * @param string $type
     * @param string $category
     * @param string $search
     * @param int $directoryId
     * @param bool $countPopularity
     * @param string $perpage
     * @return array
     */
    public static function searchCharacteristics(string $type, string $category, string $search, int $directoryId, bool $countPopularity = false, string $perpage): array
    {
        $items = [];
        $useOnlyDictionaryValues = self::getWbUseOnlyDictionaryValues($type, $category);

        if ($type) {
            $items = self::searchInWb($directoryId, $search, $type);
        }

        if (!empty($items)) {
            $items = ($countPopularity) ? $items->sum('popularity') : $items->paginate($perpage)->setPath('');
        }

        return ['items' => $items, 'useOnlyDictionaryValues' => $useOnlyDictionaryValues];
    }


    /**
     *
     * @param string $type
     * @param string $category
     * @return bool
     */
    private static function getWbUseOnlyDictionaryValues(string $type, string $category): bool {

        $characteristic = WbProductHelper::getWbCharacteristicByTypeAndProductsCategory($type, $category);

        return ($characteristic && isset($characteristic->useOnlyDictionaryValues))
            ? $characteristic->useOnlyDictionaryValues
            : self::useOnlyDictionaryValues;

    }

    /**
     * Поиск в WB
     * @param $directoryId
     * @param $search
     * @param $type
     * @return mixed
     */
    private static function searchInWb($directoryId, $search, $type)
    {
        $search = escapeRawQueryString($search);
        $feature = WbFeature::where(['directory_id' => $directoryId, 'title' => $type])->first();
        if ($feature) {
            if ($search) {
                if($type == "Возрастная группа (лет)") {
                    $items = $feature->items()->where('title', 'LIKE', DB::raw("'%$search%'"))
                    ->select([DB::raw('DISTINCT(id)'), 'title', 'popularity', 'wb_directory_id', 'has_in_ozon'])
                    ->orderBy(DB::raw("title REGEXP '^$search'"), 'desc')
                    ->orderBy('popularity', 'DESC')
                    ->orderBy('has_in_ozon', 'DESC')
                    ->orderBy('title', 'ASC');
                } else {
                    $items = $feature->items()->where('title', 'LIKE', DB::raw("'%$search%'"))
                    ->orderBy(DB::raw("title REGEXP '^$search'"), 'desc')
                    ->orderBy('popularity', 'DESC')
                    ->orderBy('has_in_ozon', 'DESC')
                    ->orderBy('title', 'ASC');
                }
            } else {
                $featureDirectoryItems = DB::table('wb_feature_directory_items')
                    ->select('item_id')
                    ->where('feature_id', '=', $feature->id)
                    ->limit(5000)
                    ->pluck('item_id');
                if (!empty($featureDirectoryItems)) {
                    $items = WbDirectoryItem::select(['id', 'title', 'popularity'])
                        ->whereIn('id', $featureDirectoryItems)
                        ->orderBy('popularity', 'DESC')
                        ->orderBy('has_in_ozon', 'DESC')
                        ->orderBy('title');
                }
            }
        }
        return $items ?? null;
    }
}
