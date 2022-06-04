<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class SearchQuery
 *
 * @package App\Models
 *
 * @property int    $id
 * @property string $name
 * @property int    $ozon_category_id
 * @property bool   $is_negative
 */
class SearchQuery extends Model
{
    /**
     * Корневые запросы
     *
     * @return BelongsToMany
     */
    public function rootQueries()
    {
        return $this->belongsToMany(RootQuery::class)->using(RootQuerySearchQuery::class);
    }

    /**
     * Связи с корневыми запросами
     *
     * @return HasMany
     */
    public function rootQueriesLinks()
    {
        return $this->hasMany(RootQuerySearchQuery::class);
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
     * История
     *
     * @return HasMany
     */
    public function histories()
    {
        return $this->hasMany(SearchQueryHistory::class);
    }

    /**
     * Место в выдаче
     *
     * @return HasOne
     */
    public function rank()
    {
        return $this->hasOne(SearchQueryRank::class);
    }

    /**
     * Товарные карточки
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(OzProduct::class);
    }

    /**
     * Аггрегированная история за последние 30 дней
     *
     * @param int|null $ozonCategoryId
     *
     * @return HasMany
     */
    public function last30daysHistoriesInCategory($ozonCategoryId)
    {
        return $this->histories()
                    ->lastNdays(30)
                    ->where(function (Builder $query) use ($ozonCategoryId) {
                        $query->where('ozon_category_id', $ozonCategoryId)
                              ->orWhereNull('ozon_category_id');
                    })
                    ->selectRaw('SUM(popularity) as summary_popularity')
                    ->selectRaw('SUM(additions_to_cart) as summary_additions_to_cart')
                    ->selectRaw('AVG(avg_price) as avg_avg_price');
    }

    /**
     * Выделить характеристику из поискового запроса
     *
     * @param string $rootQueryTitle
     *
     * @return string
     */
    public function detachCharacteristic($rootQueryTitle)
    {
        return trim(
            str_replace('  ', ' ', str_replace($rootQueryTitle, '', $this->name))
        );
    }
}
