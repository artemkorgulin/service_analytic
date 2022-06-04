<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CompanyProductInterface
{
    /**
     * Move all products from one account to another for products
     * @param int $sourceUserId
     * @param int $recipientUserId
     * @param int $accountId
     * @return bool
     */
    public static function moveProducts(int $sourceUserId, int $recipientUserId, int $accountId): bool;

    /**
     * Delete products from account and user
     * @param int $userId
     * @param int $accountId
     * @return bool
     */
    public static function deleteProducts(int $userId, int $accountId): bool;

    /**
     * Common part to create product
     * @param int $userId
     * @param int $accountId
     * @param Collection|array $temporaryProductsToCreate
     * @return bool
     */
    public static function createProduct(int $userId, int $accountId, Collection|array $temporaryProductsToCreate): bool;

    /**
     * Create products by ids from ..TemporaryProducts
     * @param int $userId
     * @param int $accountId
     * @param array $ids
     * @return bool
     */
    public static function createProductsByIds(int $userId, int $accountId, array $ids = []): bool;

}
