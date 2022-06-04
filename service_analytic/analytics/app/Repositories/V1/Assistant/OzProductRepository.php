<?php

namespace App\Repositories\V1\Assistant;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class OzProductRepository
 */
class OzProductRepository
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
        return self::getVaQuery('oz_products AS ozp');
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
