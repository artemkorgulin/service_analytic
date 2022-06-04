<?php

namespace App\Repositories\V1\Assistant;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class WbProductRepository
 */
class WbProductRepository
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
        return self::getVaQuery('wb_products AS wbp');
    }

    /**
     * @param Collection $vendorCodes
     * @return Collection
     */
    public function getProductsByVendorCodes(Collection $vendorCodes): Collection
    {
        return self::startConditions()
            ->select('id', 'nmid', 'optimization')
            ->whereIn('nmid', $vendorCodes)->get()->keyBy('nmid');
    }


    /**
     * @param int $userId
     * @return Builder
     */
    private function getProductsByUserId(int $userId): Builder
    {
        return self::startConditions()
            ->where('user_id', $userId);
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getUserProductNmids(int $userId): Collection
    {
        return $this->getProductsByUserId($userId)->pluck('nmid');
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getProductsCount(int $userId): int
    {
        return $this->getProductsByUserId($userId)->count();
    }

    /**
     * @param int $userId
     * @param string $field
     * @return int|float|null
     */
    public function getSumProductField(int $userId, string $field): int|float|null
    {
        return $this->getProductsByUserId($userId)->sum($field);
    }
}
