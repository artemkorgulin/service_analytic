<?php

namespace App\Repositories\V1\Assistant;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use StdClass;

/**
 * Class ProductRepository
 */
class OzProductAnalyticsDataRepository
{
    /**
     * @param string $tableName
     * @return Builder
     */
    public static function getVaQuery(string $tableName): Builder
    {
        return DB::connection('va')->table($tableName);
    }

    /**
     * @return Builder
     */
    public static function startConditions(): Builder
    {
        return self::getVaQuery('oz_product_analytics_data');
    }

    /**
     * @param int $vendorCode
     * @param $date
     * @return StdClass|null
     */
    public static function getSalesByOzProductAnalyticsData(int $vendorCode, $date): StdClass|null
    {
        return self::startConditions()
            ->where('sku', '=', $vendorCode)
            ->where('report_date', '=', $date)
            ->first();
    }
}
