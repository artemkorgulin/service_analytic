<?php
namespace App\Services\Wildberries;

use App\Classes\Helper;
use App\Jobs\CreateWbProductFromTemporary;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\GetProductFromWildberries;
use App\Jobs\UpdateWbOptimisation;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Models\WbProductUser;
use App\Models\WbTemporaryProduct;
use App\Contracts\CompanyProductInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;

class WildberriesCompanyProductService implements CompanyProductInterface
{

    /**
     * Move product to other user in account
     * {@inheritDoc}
     * @see CompanyProductInterface::moveProducts()
     */
    public static function moveProducts(int $sourceUserId, int $recipientUserId, int $accountId): bool
    {
        try {
            WbProductUser::query()->where([
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
            WbTemporaryProduct::onlyTrashed()->restore();
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
        return true;
    }


    /**
     * Create WbProducts by ids from WbTemporaryProducts
     * {@inheritDoc}
     * @see CompanyProductInterface::createProductsByIds()
     */
    public static function createProductsByIds(int $userId, int $accountId, array $ids = []): bool
    {
        $temporaryProductsToCreate = WbTemporaryProduct::query()->whereIn('id', $ids)->get();
        return self::createProduct($userId, $accountId, $temporaryProductsToCreate);
    }

    /**
     * Create WbProducts by ids from OzTemporaryProducts
     * @param int $userId
     * @param int $accountId
     * @param array $imtIds
     * @return bool
     */
    public static function createProductsByImtIds(int $userId, int $accountId, array $imtIds = []): bool
    {
        $temporaryProductsToCreate = WbTemporaryProduct::query()->whereIn('imt_id', $imtIds)->get();
        return self::createProduct($userId, $accountId, $temporaryProductsToCreate);
    }

    /**
     * Common part for function to create Wildberries products
     * {@inheritDoc}
     * @see CompanyProductInterface::createProduct()
     */
    public static function createProduct(int $userId, int $accountId, Collection|array $temporaryProducts): bool
    {
        try {
            foreach ($temporaryProducts as $tmpProduct) {

                $wbDeletedProduct = WbProduct::where([
                    'imt_id' => $tmpProduct->imt_id,
                ])->withTrashed()->first();

                if ($wbDeletedProduct) {

                    $wbDeletedProduct->user_id = UserService::getUserId();
                    $wbDeletedProduct->restore();
                    $wbDeletedProduct->save();

                    $data = Helper::getUsableData($wbDeletedProduct);

                    // WbSync nomenclatures
                    $nomenclatureNmIds = Helper::wbCardGetNmIds($data);
                    $nomenclatureIds = WbNomenclature::currentAccount()
                        ->whereIn('nm_id', $nomenclatureNmIds)
                        ->pluck('id')->toArray();
                    $wbDeletedProduct->nomenclatures()->sync($nomenclatureIds);

                    // Attach product in table wb_product_user
                    $wbDeletedProduct->attachUserAndAccount(UserService::getUserId(), UserService::getAccountId());

                    // Resync product in WB (SBS)
                    Bus::chain([
                        new GetProductFromWildberries($wbDeletedProduct), // Get fresh data for new WB product
                        new UpdateWbOptimisation($wbDeletedProduct), // After that update optimisations
                    ])->dispatch();

                } else {

                    $newWbProduct = WbProduct::createWbProductWithUserAndAccount($tmpProduct, UserService::getUserId(), UserService::getAccountId());

                    // Perform SBS
                    Bus::chain([
                        new GetProductFromWildberries($newWbProduct), // Get fresh data for new WB product
                        new CreateWbProductFromTemporary($tmpProduct, $newWbProduct), // Update characteristics in database and in nomenclatures
                        new UpdateWbOptimisation($newWbProduct), // After that update optimisations
                        new DashboardAccountUpdateJob(
                            UserService::getUserId(),
                            UserService::getAccountId(),
                            UserService::getCurrentAccountPlatformId()),])->dispatch();

                }
            }
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }

        return true;
    }
}
