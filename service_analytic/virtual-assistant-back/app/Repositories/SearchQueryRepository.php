<?php
namespace App\Repositories;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SearchQueryRepository
{
    /**
     * Найти подходящие поисковые запросы по категории Ozon и названию корневого запроса
     *
     * @param integer $ozonCategoryId
     * @param string $rootQueryTitle
     *
     * @return Collection|bool
     */
    public static function findByRootQueryInCategory($ozonCategoryId, $rootQueryTitle)
    {
        // Ищем сначала по корневым запросам
        $rootQueries = RootQueryRepository::findInCategory($ozonCategoryId, $rootQueryTitle);

        // Возвращаем список поисковых запросов
        return SearchQuery::query()
                          ->where('is_negative', false)
                          ->join('root_query_search_query', 'root_query_search_query.search_query_id', 'search_queries.id')
                          ->whereIn('root_query_search_query.root_query_id', $rootQueries->modelKeys())
                          ->where('search_queries.name', '!=', $rootQueryTitle)
                          ->orderBy('root_query_search_query.rating', 'desc')
                          ->orderBy('search_queries.name', 'asc')
                          ->groupBy('search_queries.name')
                          ->select(
                              'root_query_search_query.id as id',
                              'root_query_search_query.root_query_id',
                              'root_query_search_query.search_query_id',
                              'search_queries.name',
                              'root_query_search_query.rating'
                          )
                          ->get();
    }

    /**
     * Найти поисковые запросы по id
     *
     * @param int[] $ids
     * @param int   $limit
     *
     * @return Collection
     */
    public static function findByLinksIds($ids, $limit = 10)
    {
        return SearchQuery::query()
                          ->whereIn('root_query_search_query.id', $ids)
                          ->join('root_query_search_query', 'root_query_search_query.search_query_id', 'search_queries.id')
                          ->orderBy('root_query_search_query.rating', 'desc')
                          ->orderBy('search_queries.name', 'asc')
                          ->select(
                              'root_query_search_query.id as id',
                              'root_query_search_query.root_query_id',
                              'root_query_search_query.search_query_id',
                              'search_queries.name',
                              'root_query_search_query.rating'
                          )
                          ->limit($limit)
                          ->get();
    }

    /**
     * Найти подходящие поисковые запросы по названию и корневому запросу
     *
     * @param integer $rootQueryId
     * @param string  $searchQueryTitle
     *
     * @return SearchQuery|bool
     */
    public static function findFirstByTitleWithRootQuery($rootQueryId, $searchQueryTitle)
    {
        return SearchQuery::query()
                          ->where('name', $searchQueryTitle)
                          ->when($rootQueryId, function(Builder $query) use ($rootQueryId) {
                              $query->whereHas('rootQueries', function(Builder $query) use ($rootQueryId) {
                                  return $query->whereIn('root_query_id', $rootQueryId);
                              });
                          })
                          ->first();
    }

    /**
     * Найти подходящие поисковые запросы по названию и корневому запросу
     *
     * @param integer $ozonCategoryId
     * @param string  $searchQueryTitle
     *
     * @return Collection
     */
    public static function findByTitleInCategory($ozonCategoryId, $searchQueryTitle)
    {
        return SearchQuery::query()
                          ->when($ozonCategoryId, function(Builder $query) use ($ozonCategoryId) {
                              return $query->whereHas('rootQueries', function(Builder $query) use ($ozonCategoryId) {
                                  $query->where('ozon_category_id', $ozonCategoryId);
                              });
                          })
                          ->where('name', $searchQueryTitle)
                          ->select('id', 'name')
                          ->get();
    }
}
