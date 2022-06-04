<?php

namespace App\Services\Wildberries;

use App\Classes\Helper;
use App\Jobs\CreateWbProductFromTemporary;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\GetProductFromWildberries;
use App\Jobs\UpdateWbOptimisation;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use App\Services\Interfaces\Wildberries\WildberriesShowProductServiceInterface;
use App\Services\UserService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class WildberriesControlProductsService implements WildberriesShowProductServiceInterface
{
    public function resync($client, $accountId, $id)
    {
        $product = WbProduct::where([
            'id' => $id,
            'account_id' => $accountId,
        ])->first();
        if ($product) {
            // клиент уже есть отправляем его на синхронизацию
            $response = $client->cardUpdate($product->data);
            return response()->json($response);
        }
        return null;
    }

    public function store($productForStore)
    {
        $product = WbProduct::create($productForStore);
        $product->save();
        dispatch(new UpdateWbOptimisation($product));
    }

    /**
     * Activate product - create Wildberries product from temporary
     * @param int[] $idsForQuery
     * @return array
     */
    public function activateNotActiveProducts(array $idsForQuery)
    {
        try {
            $temporaryProducts = WbTemporaryProduct::whereIn('id', $idsForQuery)->currentAccount()->get();
            foreach ($temporaryProducts as $tmpProduct) {
                $wbDeletedProduct = WbProduct::where([
                    'imt_id' => $tmpProduct->imt_id,
                ])->withTrashed()->first();
                if ($wbDeletedProduct) {
                    $wbDeletedProduct->user_id = UserService::getUserId();
                    $wbDeletedProduct->restore();
                    $data = Helper::getUsableData($wbDeletedProduct);
                    // WbSync nomenclatures
                    $nomenclatureNmIds = Helper::wbCardGetNmIds($data);
                    $nomenclatureIds = WbNomenclature::currentAccount()
                        ->whereIn('nm_id', $nomenclatureNmIds)
                        ->pluck('id')->toArray();
                    $wbDeletedProduct->nomenclatures()->sync($nomenclatureIds);
                    // Attach product in table wb_product_user
                    $wbDeletedProduct->attachUserAndAccount(UserService::getUserId(), UserService::getAccountId());
                    // Resync Wildberries product
                    GetProductFromWildberries::dispatch($wbDeletedProduct);
                    // Resync product in WB from
                    Bus::chain([
                        new GetProductFromWildberries($wbDeletedProduct), // Get fresh data for new WB product
                        new UpdateWbOptimisation($wbDeletedProduct), // After that update optimisations
                    ])->dispatch();
                } else {
                    $newWbProduct = WbProduct::createWbProductWithUserAndAccount($tmpProduct, UserService::getUserId(), UserService::getAccountId());
                    Bus::chain([
                        new GetProductFromWildberries($newWbProduct), // Get fresh data for new WB product
                        new CreateWbProductFromTemporary($tmpProduct, $newWbProduct), // Update characteristics in database and in nomenclatures
                        new UpdateWbOptimisation($newWbProduct), // After that update optimisations
                        new DashboardAccountUpdateJob(
                            UserService::getUserId(),
                            UserService::getAccountId(),
                            UserService::getCurrentAccountPlatformId()
                        ),
                    ])->dispatch();
                }
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage(), 'code' => 400];
        }
        return ['status' => 'success', 'data' => $idsForQuery];
    }


    /**
     * Delete product from observation
     * @param $ids
     * @return array|string[]
     */
    public function destroy($ids)
    {
        $userProductsImtIds = WbProduct::query()->select('wb_products.imt_id')->currentUserAndAccount()
            ->whereIn('id', $ids)->pluck('imt_id');

        // Deactivate and delete
        $deletedProducts = WbProduct::whereIn('wb_products.imt_id', $userProductsImtIds)->currentUserAndAccount();

        DB::table('wb_product_user')->whereIn('imt_id', $userProductsImtIds)->where([
            'user_id' => UserService::getUserId(),
            'account_id' => UserService::getCurrentAccountWildberriesId(),
        ])->update(['deleted_at' => \Carbon\Carbon::now()]);

        foreach ($userProductsImtIds as $userProductsImtId) {
            if (DB::table('wb_product_user')->where([
                    'imt_id' => $userProductsImtId
                ])->whereNull('deleted_at')->count() === 0) {
                DB::table('wb_product_user')->where([
                    'imt_id' => $userProductsImtId])->delete();
                DB::table('wb_products')->where([
                    'imt_id' => $userProductsImtId])->delete();
                // for old algorithm repair deleted products
                DB::table('wb_temporary_products')->where([
                    'imt_id' => $userProductsImtId])->update(['deleted_at' => null]);
            }
        }

        $deletedProducts->delete();

        WbTemporaryProduct::withTrashed()->currentAccount()->whereIn('imt_id', $userProductsImtIds)->restore();

        $diff = count($ids) - count($userProductsImtIds);

        if ($diff > 0) {
            $message = $diff == 1 ? 'Товар не найден' : 'Товары не найдены';
            return ['status' => 'error', 'message' => $message, 'code' => 404];
        } else {
            $message = count($userProductsImtIds) == 1 ? 'Товар удален (скрыт)' : 'Товары удалены (скрыты)';

            if (app()->runningInConsole() === false) {
                DashboardAccountUpdateJob::dispatch(
                    UserService::getUserId(),
                    UserService::getAccountId(),
                    UserService::getCurrentAccountPlatformId()
                )->delay(now()->addMinute(2));
            }

            return ['status' => 'success', 'data' => $message];
        }
    }
}
