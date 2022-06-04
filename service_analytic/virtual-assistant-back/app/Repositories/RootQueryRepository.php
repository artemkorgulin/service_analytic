<?php
namespace App\Repositories;

use App\Models\RootQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RootQueryRepository
{
    /**
     * Найти все корневые запросы, содержащие часть запроса в себе
     *
     * @param string $title
     *
     * @return Collection
     */
    public static function findLikeTitle($title)
    {
        // Ищем в базе совпадения по корневым запросам
        return RootQuery::query()
                        ->where(function (Builder $query) use ($title) {
                            $query->where('name', 'like', '%'.$title.'%');
                            $query->orWhere(function (Builder $query) use ($title) {
                                $query->whereHas('oz_aliases', function (Builder $query) use ($title) {
                                    $query->where('name', 'like', '%'.$title.'%');
                                });
                            });
                        })
                        ->where('is_visible', true)
                        ->get();
    }

    /**
     * Найти все корневые запросы, содержащие часть запроса в себе или своем синониме, в категории
     *
     * @param integer $ozonCategoryId
     * @param string  $title
     *
     * @return Collection
     */
    public static function findLikeTitleInCategory($ozonCategoryId, $title)
    {
        // Ищем в базе совпадения по корневым запросам или по синонимам
        return RootQuery::query()
                        ->where('ozon_category_id', $ozonCategoryId)
                        ->where(function (Builder $query) use ($title) {
                            $query->where('name', 'like', '%'.$title.'%');
                            $query->orWhere(function (Builder $query) use ($title) {
                                $query->whereHas('oz_aliases', function (Builder $query) use ($title) {
                                    $query->where('name', 'like', '%'.$title.'%');
                                });
                            });
                        })
                        ->get();
    }

    /**
     * Найти все корневые запросы по базе корневых,
     * если не нашел - то соответствующий синониму
     *
     * @param integer $ozonCategoryId
     * @param string  $title
     * @return Collection
     */
    public static function findInCategory($ozonCategoryId, $title)
    {
        // Ищем в базе совпадения по корневым запросам или по синонимам
        // Ищем сначала по корневым запросам
        $rootQueries = RootQuery::query()
                                ->when($ozonCategoryId, function (Builder $query) use ($ozonCategoryId) {
                                    $query->where('ozon_category_id', $ozonCategoryId);
                                })
                                ->where('name', $title)
                                //->with('searchQueries')
                                ->select('id')
                                ->get();

        // Если не нашлось
        if( $rootQueries->count() == 0 )
        {
            // То ищем корневые запросы по синонимам
            $rootQueries = RootQuery::query()
                                    ->when($ozonCategoryId, function (Builder $query) use ($ozonCategoryId) {
                                        $query->where('ozon_category_id', $ozonCategoryId);
                                    })
                                    ->whereHas('oz_aliases', function (Builder $query) use ($title) {
                                        $query->where('name', $title);
                                    })
                                    //->with('searchQueries')
                                    ->select('id')
                                    ->get();
        }

        return $rootQueries;
    }
}
