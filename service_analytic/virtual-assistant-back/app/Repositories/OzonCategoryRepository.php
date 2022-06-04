<?php

namespace App\Repositories;

use App\Models\OzCategory;
use App\Models\OzDataCategory;
use App\Repositories\Common\CommonProductRepository;
use App\Repositories\Ozon\OzonProductStatusRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as DBBuilder;

class OzonCategoryRepository
{
    /**
     * Получить все категории Озон
     *
     * @return Collection
     */
    public static function getAll()
    {
        return OzDataCategory::all()->pluck('name', 'id');
    }

    /**
     * Получить категории Озон, которые содержат корневой запрос
     *
     * @param string $rootQueryTitle
     *
     * @return Collection
     */
    public static function findByRootQueryTitle($rootQueryTitle)
    {
        return OzDataCategory::query()
            ->whereHas('rootQueries', function (Builder $query) use ($rootQueryTitle) {
                $query->where(function (Builder $query) use ($rootQueryTitle) {
                    $query->where('name', $rootQueryTitle);
                    $query->orWhere(function (Builder $query) use ($rootQueryTitle) {
                        $query->whereHas('oz_aliases', function (Builder $query) use ($rootQueryTitle) {
                            $query->where('name', $rootQueryTitle);
                        });
                    });
                });
            })
            ->orderBy('id', 'asc')
            ->pluck('name', 'id');
    }


    /**
     * @param int $userId
     * @param int $accountId
     * @param string|null $search
     * @return DBBuilder
     */
    public function getAccountProductCategory(
        int $userId,
        int $accountId,
        string $search = ''
    ) {
        $model = new OzCategory();

        $categoryQuery = DB::table($model->getTable() . ' as category')
            ->select('category.id', 'category.name', 'category.parent_id', 'parent.name as parent_name')
            ->leftJoin(
                $model->getTable() . ' as parent',
                'parent.id',
                'category.parent_id'
            )->whereExists(function ($query) use ($userId, $accountId) {
                $query->select('category_id')
                    ->from('oz_products')
                    ->where([
                        'status_id' => OzonProductStatusRepository::PRODUCT_STATUS_VERIFIED_ID,
                    ])
                    ->whereRaw('oz_products.category_id = category.id')
                    ->whereExists(function ($query) use ($userId, $accountId) {
                        $query->select('external_id')
                            ->from('oz_product_user')
                            ->where([
                                'user_id' => $userId,
                                'account_id' => $accountId,
                            ])->whereNull('oz_products.deleted_at')
                            ->whereRaw('oz_product_user.external_id = oz_products.external_id');
                    });
            });

        if ($search) {
            $categoryQuery->where(function ($query) use ($search, $model) {
                $query->where(
                    'category.name',
                    'LIKE',
                    '%' . $search . '%')
                    ->orWhereExists(
                        function ($query) use ($model, $search) {
                            $query->from($model->getTable() . ' as parent')
                                ->select('parent.name', 'parent.id')
                                ->where(
                                    'parent.name',
                                    'LIKE',
                                    '%' . $search . '%')
                                ->whereRaw('category.parent_id = parent.id');
                        });
            });
        }

        return $categoryQuery;
    }
}
