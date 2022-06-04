<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class RootQuerySearchQuery
 *
 * @package App\Models
 *
 * @property int    $root_query_id
 * @property int    $search_query_id
 * @property int    $popularity
 * @property int    $additions_to_cart
 * @property int    $avg_price
 * @property int    $products_count
 * @property int    $rating
 * @property double $filtering_ratio
 */
class RootQuerySearchQuery extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Корневой запрос
     *
     * @return BelongsTo
     */
    public function rootQuery()
    {
        return $this->belongsTo(RootQuery::class);
    }

    /**
     * Поисковый запрос
     *
     * @return BelongsTo
     */
    public function searchQuery()
    {
        return $this->belongsTo(SearchQuery::class);
    }

    /**
     * Характеристики
     *
     * @return belongsToMany
     */
    public function characteristics()
    {
        return $this->belongsToMany(Characteristic::class);
    }

    /**
     * Расчитать рейтинг поискового запроса
     *
     * @return float
     */
    public function calcRating()
    {
        return $this->products_count
            ? round(pow($this->additions_to_cart, 2) / $this->products_count, 2)
            : 0;
    }

    /**
     * Расчитать Коэффициент отсеивания поискового запроса
     *
     * @param integer $rootQueryCost
     */
    public function calcFilteringRatio($rootQueryCost)
    {
        $this->filtering_ratio = $rootQueryCost
            ? round(100 * $this->additions_to_cart * $this->avg_price / $rootQueryCost, 2)
            : 0;
    }
}
