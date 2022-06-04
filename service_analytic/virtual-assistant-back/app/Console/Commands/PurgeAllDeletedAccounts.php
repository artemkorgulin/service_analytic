<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Services\InnerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeAllDeletedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all data from accounts which were deleted';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->deleteAllOldOzonAccountData();
        $this->deleteAllOldWildberriesAccountData();
        return 0;
    }

    /**
     * Get all accounts for Ozon
     * @return array
     */
    private static function collectAllOzonAccount()
    {
        return DB::table('oz_temporary_products')
            ->select('account_id')
            ->groupBy('account_id')
            ->pluck('account_id')
            ->toArray();
    }

    /**
     * Get all accounts for Wildberries
     * @return array
     */
    private static function collectAllWildberriesAccount()
    {
        return DB::table('wb_temporary_products')
            ->select('account_id')
            ->groupBy('account_id')
            ->pluck('account_id')
            ->toArray();
    }

    /**
     * Check which accounts not exist more
     * @return array
     */
    private static function checkWhichAccountNotExistsMore(array $accountIds)
    {
        $innerService = new InnerService();
        $deletedAccountIds = [];
        foreach ($accountIds as $accountId) {
            $account = $innerService->getAccount($accountId);
            if (!$account) {
                $deletedAccountIds[] = $accountId;
            }
        }
        return $deletedAccountIds;
    }

    /**
     * Delete all old data for Ozon main function
     */
    public function deleteAllOldOzonAccountData()
    {
        $accountIdsForDelete = self::checkWhichAccountNotExistsMore(self::collectAllOzonAccount());
        foreach ($accountIdsForDelete as $idForDelete) {
            $this->alert("Delete data for Ozon Account {$idForDelete}");
            $this->deteteOzonAccount($idForDelete);
        }
    }

    /**
     * Delete all old data for Wildberries main function
     */
    public function deleteAllOldWildberriesAccountData()
    {
        $accountIdsForDelete = self::checkWhichAccountNotExistsMore(self::collectAllWildberriesAccount());
        foreach ($accountIdsForDelete as $idForDelete) {
            $this->alert("Delete data for Wildberries Account {$idForDelete}");
            $this->deteteWildberriesAccount($idForDelete);
        }
    }

    /**
     * Delete Ozon account
     * @param $accountId
     */
    private function deteteOzonAccount($accountId)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // table oz_products
        $productIds = DB::table('oz_products')->select('id')
            ->where('account_id', $accountId)->pluck('id')->toArray();
        try {
            $ids = DB::table('oz_product_change_history')
                ->whereIn('product_id', $productIds)->pluck('id')->toArray();
            DB::table('oz_product_feature_history')->whereIn('history_id', $ids)->delete();
            DB::table('oz_product_feature_error_history')->whereIn('history_id', $ids)->delete();
            DB::table('oz_product_price_change_history')->whereIn('product_history_id', $ids)->delete();
            DB::table('oz_product_price_change_history')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_product_change_history')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_products_features')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_trigger_remove_from_sale')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_trigger_change_min_photos')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_trigger_change_min_reviews')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_product_positions_history_graph')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_product_positions_history')->whereIn('product_id', $productIds)->delete();
            DB::table('oz_products')->where('account_id', $accountId)->delete();
            DB::table('oz_temporary_products')->where('account_id', $accountId)->delete();
        } catch (\Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }


    /**
     *  Delete Wildberries account
     * @param $accountId
     */
    private function deteteWildberriesAccount($accountId)
    {
        DB::table('wb_products')->where('account_id', $accountId)->delete();
        DB::table('wb_temporary_products')->where('account_id', $accountId)->delete();
        DB::table('wb_nomenclatures')->where('account_id', $accountId)->delete();
    }
}
