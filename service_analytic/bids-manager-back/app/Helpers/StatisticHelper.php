<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

class StatisticHelper
{
    /**
     * Сформировать запрос итогов статистики
     * @param Builder $query
     * @param string $table
     * @return Builder
     */
    public static function addCharacteristicsSelect(Builder $query, $table)
    {
        return $query
                ->selectRaw("SUM($table.popularity) AS popularity")
                ->selectRaw("SUM($table.shows) AS shows")
                ->selectRaw("(100 * shows / popularity) as purchased_shows")
                ->selectRaw("SUM($table.clicks) AS clicks")
                ->selectRaw("(100 * clicks / shows) as ctr")
                ->selectRaw("(1000 * cost / shows) as avg_1000_shows_price")
                ->selectRaw("(cost / clicks) as avg_click_price")
                ->selectRaw("SUM($table.cost) AS cost")
                ->selectRaw("SUM($table.orders) AS orders")
                ->selectRaw("SUM($table.profit) AS profit")
                ->selectRaw("(cost / profit) * 100 as  drr")
                ->selectRaw("(cost / orders) as cpo")
                ->selectRaw("(cost / profit) as acos");
    }
}
