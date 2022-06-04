<?php
namespace App\Repositories;

use App\Models\RootQuerySearchQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RootQuerySearchQueryRepository
{
    /**
     * Найти подходящие поисковые запросы по названию и корневому запросу
     *
     * @param integer $ozonCategoryId
     * @param string $searchQueryTitle
     *
     * @return RootQuerySearchQuery[]|Collection
     */
    public static function findByTitleInCategory($ozonCategoryId, $searchQueryTitle)
    {
        return RootQuerySearchQuery::query()
                                   ->whereHas('rootQuery', function(Builder $query) use ($ozonCategoryId) {
                                       $query->where('ozon_category_id', $ozonCategoryId);
                                   })
                                   ->whereHas('searchQuery', function(Builder $query) use ($searchQueryTitle) {
                                       $query->where('name', $searchQueryTitle);
                                   })
                                   ->select('id', 'search_query_id', 'additions_to_cart')
                                   ->get();
    }

    /**
     * Найти подходящие поисковые запросы по названию и корневому запросу
     *
     * @param integer[] $ids
     *
     * @return RootQuerySearchQuery[]|Collection
     */
    public static function findByIds($ids)
    {
        return RootQuerySearchQuery::query()
                                   ->whereIn('id', $ids)
                                   ->select('id', 'search_query_id', 'additions_to_cart')
                                   ->get();
    }
}
