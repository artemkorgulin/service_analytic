<?php

namespace App\Repositories\V2\Product;

use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

/**
 * Class CategoryRepository
 *
 * @package App\Repositories\V2\Product
 */
class CategoryRepository
{
    public static function startConditions()
    {
        return BaseRepository::getVaQuery('oz_categories AS oc');
    }

    /**
     * @param int $categoryId
     * @return mixed
     */
    public static function getCategory(int $categoryId)
    {
        return self::startConditions()
            ->where('external_id', $categoryId)
            ->first();
    }

    /**
     * @return Collection
     */
    public static function getParentList(): Collection
    {
        return self::startConditions()
            ->select('external_id AS id', 'name')
            ->where('parent_id', null)
            ->get();
    }

    /**
     * @param int $categoryId
     * @return mixed
     */
    public static function getProductCategory(int $productId, ?int $accountId = null)
    {
        return self::startConditions()
            ->select('oc.*')
            ->leftJoin('oz_temporary_products AS otp', 'oc.id', '=', 'otp.category_id')
            ->where(['otp.external_id' => $productId, 'otp.account_id' => $accountId])
            ->first();
    }

    /**
     * Категория верхнего уровня
     *
     * @return \stdClass
     */
    public static function getTopCategory(int $categoryId)
    {
        $parentCategory = self::getCategory($categoryId);

        while ($parentCategory->parent_id) {
            $parentCategory = self::getCategory($parentCategory->external_id);
        }

        return $parentCategory;
    }

    /**
     * Get category id in VA
     *
     * @param int $id
     * @return mixed
     */
    public static function getVaCategoryId(int $id)
    {
        return optional(self::startConditions()
            ->where('external_id', $id)
            ->first())->id;
    }
}
