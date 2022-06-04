<?php
namespace App\Services\Ozon;

use App\Jobs\CreateOzProductFromTemporary;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\OzLoadAnalyticsData;
use App\Jobs\UpdateOzOptimisation;
use App\Models\OzProduct;
use App\Models\OzProductUser;
use App\Models\OzTemporaryProduct;
use App\Models\WbProduct;
use App\Models\WbProductUser;
use App\Contracts\CompanyProductInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Services\V2\ProductTrackingService;

class OzonCompanyProductService implements CompanyProductInterface
{
    /**
     * Move all Ozon products from one user in account to other
     * {@inheritDoc}
     * @see CompanyProductInterface::moveProducts()
     */
    public static function moveProducts(int $sourceUserId, int $recipientUserId, int $accountId): bool
    {
        try {
            OzProductUser::query()->where([
                'user_id' => $sourceUserId,
                'account_id' => $accountId])->update(['user_id' => $recipientUserId]);
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
        return true;
    }

    /**
     * Delete Wildberries products from account and user
     * {@inheritDoc}
     * @see CompanyProductInterface::deleteProducts()
     */
    public static function deleteProducts(int $userId, int $accountId): bool
    {
        try {
            if ($userId === 0) {
                WbProductUser::query()->where(['account_id' => $accountId])->forceDelete();
            } else {
                WbProductUser::query()->where([
                    'user_id' => $userId,
                    'account_id' => $accountId])->forceDelete();
            }
            WbProduct::query()->without(['usersAndAccount'])->delete();
            OzTemporaryProduct::onlyTrashed()->restore();
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
        return true;
    }

    /**
     * Create OzProduts by ids from OzTemporaryProducts
     * {@inheritDoc}
     * @see CompanyProductInterface::createProductsByIds()
     */
    public static function createProductsByIds(int $userId, int $accountId, array $ids = []): bool
    {
        $temporaryProductsToCreate = OzTemporaryProduct::query()->whereIn('id', $ids)->get();
        return self::createProduct($userId, $accountId, $temporaryProductsToCreate);
    }

    /**
     * Create OzProduts by external ids from OzTemporaryProducts
     * @param int $userId
     * @param int $accountId
     * @param array $externalIds
     * @return bool
     */
    public static function createProductsByExternalIds(int $userId, int $accountId, array $externalIds = []): bool
    {
        $temporaryProductsToCreate = OzTemporaryProduct::query()->whereIn('external_id', $externalIds)->get();
        return self::createProduct($userId, $accountId, $temporaryProductsToCreate);
    }

    /**
     * Common part to create Ozon product
     * {@inheritDoc}
     * @see CompanyProductInterface::createProduct()
     */
    public static function createProduct(int $userId, int $accountId, Collection|array $temporaryProductsToCreate): bool
    {
        try {
            foreach ($temporaryProductsToCreate as $temporaryProductToCreate) {

                $externalId = $temporaryProductToCreate->externalId;
                $product = OzProduct::query()->where('external_id', $externalId)->withTrashed()->first();

                if ($product && $product->trashed()) {

                    $product->restore();
                    $product->attachUserAndAccount($userId, $accountId);
                    OzLoadAnalyticsData::dispatch($product);

                } elseif (!$product) {

                    // Initially create and upload temporary product
                    $sku = $temporaryProductsToCreate->sku;
                    $productTrackingService = new ProductTrackingService($userId, $accountId);
                    $product = $productTrackingService->trackProduct($sku);
                    $product->update([
                        'quantity' => $temporaryProductsToCreate->quantity,
                    ]);
                    $product->attachUserAndAccount(UserService::getUserId(), UserService::getAccountId());

                    CreateOzProductFromTemporary::dispatch($temporaryProductsToCreate, $product)->onQueue('default_long');
                    UpdateOzOptimisation::dispatch($product)->onQueue('default_long');

                    DashboardAccountUpdateJob::dispatch(
                        UserService::getUserId(),
                        UserService::getAccountId(),
                        UserService::getCurrentAccountPlatformId()
                    )->delay(now()->addMinute(2));

                }
            }

        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
        return true;
    }
}
