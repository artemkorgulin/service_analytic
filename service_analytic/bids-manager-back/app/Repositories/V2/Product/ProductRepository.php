<?php

namespace App\Repositories\V2\Product;

use App\Repositories\BaseRepository;
use Illuminate\Database\Query\Builder;

/**
 * Class ProductRepository
 *
 * @package App\Repositories\Frontend\Product
 */
class ProductRepository
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_COUNT = 25;

    public static function startConditions()
    {
        return BaseRepository::getVaQuery('oz_temporary_products AS otp');
    }

    /**
     * @param int $categoryId
     * @return mixed
     */
    public function getProduct(int $id, int $userId)
    {
        return self::startConditions()
            ->where(['user_id' => $userId, 'external_id' => $id])
            ->first();
    }

    public function getProducts(array $ids, int $userId)
    {
        return self::startConditions()
            ->where('user_id', $userId)
            ->whereIn('external_id', $ids)
            ->get();
    }

    public function searchProduct(int|string $search, int $userId)
    {
        return self::startConditions()
            ->where('user_id', '=', $userId)
            ->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%$search%")
                    ->orWhere('brand', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%")
                    ->orWhere('external_id', 'like', "%$search%")
                    ->orWhere('category', 'like', "%$search%");
            });
    }
}
