<?php

namespace App\Repositories\V1\Assistant;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class EscrowHashRepository
 */
class EscrowHashRepository
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
        return self::getVaQuery('escrow_hashes');
    }

    /**
     * @param int $productId
     * @param int $nmId
     * @return array
     */
    public function getEscrowImageHashes(int $productId, int $nmId): array
    {
        return self::startConditions()
            ->select('name', 'image_hash', 'nmid')
            ->where('product_id', '=', $productId)
            ->where('nmid', $nmId)
            ->pluck('image_hash')
            ->toArray();
    }
}
