<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SearchQueryHistory
 *
 * @package App\Models
 *
 * @property int    $search_query_id
 * @property int    $ozon_category_id
 * @property int    $popularity
 * @property int    $additions_to_cart
 * @property int    $avg_price
 * @property int    $products_count
 * @property int    $rating
 * @property string $parsing_date
 */
class SearchQueryHistory extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'parsing_date'
    ];

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
     * За последние 30 дней
     *
     * @param Builder $query
     * @param int     $days
     *
     * @return Builder
     */
    public function scopeLastNdays($query, $days = 30)
    {
        return $query->whereDate('parsing_date', '>=', Carbon::now()->subDays($days));
    }
}
