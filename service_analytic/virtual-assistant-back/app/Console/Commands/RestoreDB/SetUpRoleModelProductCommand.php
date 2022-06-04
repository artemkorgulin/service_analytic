<?php

namespace App\Console\Commands\RestoreDB;

use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use \Illuminate\Database\Query\Builder;

class SetUpRoleModelProductCommand extends Command
{
    const TABLE_SET_UP_ARRAY = [
        'ozon' => [
            'products' => 'oz_products',
            'user_product_table' => 'oz_product_user',
            'relation_field' => 'external_id',
            'tmp_products' => 'oz_temporary_products',
        ],
        'wildberries' => [
            'products' => 'wb_products',
            'user_product_table' => 'wb_product_user',
            'relation_field' => 'imt_id',
            'tmp_products' => 'wb_temporary_products',
        ],
    ];

    /**
     * The name and signature of the console command.
     *set_up_db:role_model_product
     * @var string
     */
    protected $signature = 'set_up_db:role_model_product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда восстанавливает связь товаров в ролевой модели, которая была поломана из за ошибок, при удалении или создании ее.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            foreach (self::TABLE_SET_UP_ARRAY as $key => $table) {
                $this->info(sprintf('Начинаем восстановление %s', $key));
                $this->deleteBrokenProduct(
                    $table['products'],
                    $table['user_product_table'],
                    $table['relation_field'],
                    $table['tmp_products']
                );
            }
            $this->info(sprintf('Восстановление законеченно.'));

            return self::SUCCESS;
        } catch (\Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param string $productTable
     * @param string $userProductTable
     * @param string $relation
     * @return void
     */
    public function deleteBrokenProduct(
        string $productTable,
        string $userProductTable,
        string $relation,
        string $tmpProducts
    ): void {
        ModelHelper::transaction(function () use ($productTable, $userProductTable, $relation, $tmpProducts) {
            $activeProductsDeletedRelation = $this->getProductWrongDeleted($productTable, $userProductTable, $relation);

            $deletedProductsActiveRelation = $this->getProductWrongDeleted(
                $productTable,
                $userProductTable,
                $relation,
                false
            );

            $deletedOnMainTableAndTmp = $this->getProductOnMainAndTmpTableDeleted(
                $productTable,
                $tmpProducts,
                $relation);

            $relationForceDeletedProducts = $this->getRelationForceDeletedProducts(
                $productTable,
                $userProductTable,
                $relation);

            $relations = $this->getDuplicatedRelationProducts($userProductTable, $relation);

            $allProducts = array_merge(
                $activeProductsDeletedRelation->pluck($relation)->toArray(),
                $deletedProductsActiveRelation->pluck($relation)->toArray(),
                $relations->pluck($relation)->toArray(),
                $deletedOnMainTableAndTmp->pluck($relation)->toArray(),
                $relationForceDeletedProducts->pluck($relation)->toArray()
            );

            $allProducts = array_unique($allProducts);

            $this->info(sprintf('Найдено %d поломанных записи.', count($allProducts)));

            if (count($allProducts) !== 0) {
                // Set deleted all tracking products, for a new addition.
                DB::table($productTable)
                    ->whereIn($relation, $allProducts)
                    ->update(['deleted_at' => now()->format('Y-m-d h:i:s')]);

                // Restoration of temporary products for a new addition.
                DB::table($tmpProducts)->whereIn($relation, $allProducts)->update(['deleted_at' => null]);

                // Remove all broken relations.
                DB::table($userProductTable)->whereIn($relation, $allProducts)->delete();
            }
        });
    }

    /**
     * @param string $productTable
     * @param string $userProductTable
     * @param string $relation
     * @param string $tmpProducts
     * @return Builder
     */
    public function getProductWrongDeleted(
        string $productTable,
        string $userProductTable,
        string $relation,
        bool $reverseDeleted = true
    ): Builder {
        return DB::table($productTable)
            ->selectRaw(sprintf('%1$s.%2$s, %1$s.deleted_at', $productTable, $relation))
            ->when($reverseDeleted, function ($query) use ($productTable) {
                return $query->whereNull($productTable . '.deleted_at');
            }, function ($query) use ($productTable) {
                return $query->whereNotNull($productTable . '.deleted_at');
            })
            ->whereExists(function ($query) use ($productTable, $userProductTable, $relation, $reverseDeleted) {
                $query->selectRaw(sprintf('%1$s.%2$s, %1$s.deleted_at', $userProductTable, $relation))
                    ->from($userProductTable)
                    ->when($reverseDeleted, function ($query) use ($userProductTable) {
                        return $query->whereNotNull($userProductTable . '.deleted_at');
                    }, function ($query) use ($userProductTable) {
                        return $query->whereNull($userProductTable . '.deleted_at');
                    })
                    ->whereRaw(sprintf('%1$s.%2$s = %3$s.%2$s', $userProductTable, $relation, $productTable));
            });
    }

    /**
     * @param string $productTable
     * @param string $tmpProducts
     * @param string $relation
     * @return Builder
     */
    public function getProductOnMainAndTmpTableDeleted(
        string $productTable,
        string $tmpProducts,
        string $relation
    ): Builder {
        return DB::table($productTable)
            ->selectRaw(sprintf('%1$s.%2$s, %1$s.deleted_at', $productTable, $relation))
            ->whereNotNull($productTable . '.deleted_at')
            ->whereExists(function ($query) use ($productTable, $tmpProducts, $relation) {
                $query->selectRaw(sprintf('%1$s.%2$s, %1$s.deleted_at', $tmpProducts, $relation))
                    ->from($tmpProducts)
                    ->whereNotNull($tmpProducts . '.deleted_at')
                    ->whereRaw(sprintf('%1$s.%2$s = %3$s.%2$s', $tmpProducts, $relation, $productTable));
            });
    }

    /**
     * @param string $productTable
     * @param string $userProductTable
     * @param string $relation
     * @return Builder
     */
    public function getRelationForceDeletedProducts(
        string $productTable,
        string $userProductTable,
        string $relation
    ): Builder {
        return DB::table($userProductTable)
            ->selectRaw(sprintf('%1$s.%2$s, %1$s.account_id, %1$s.deleted_at', $userProductTable, $relation))
            ->whereNull($userProductTable . '.deleted_at')
            ->whereNotExists(function ($query) use ($productTable, $userProductTable, $relation) {
                $query->selectRaw(sprintf('%1$s.%2$s, %1$s.account_id', $productTable, $relation))
                    ->from($productTable)
                    ->whereRaw(
                        sprintf(
                            '%1$s.%2$s = %3$s.%2$s AND %1$s.account_id = %3$s.account_id',
                            $productTable,
                            $relation,
                            $userProductTable
                        )
                    );
            });
    }

    /**
     * @param string $userProductTable
     * @param string $relation
     * @return Builder
     */
    public function getDuplicatedRelationProducts(string $userProductTable, string $relation): Builder
    {
        return DB::table($userProductTable)
            ->selectRaw(sprintf('account_id, %s, count(%s)', $relation, $relation))
            ->groupBy('account_id', $relation)
            ->havingRaw(sprintf('count(%s) > 1', $relation));
    }
}
