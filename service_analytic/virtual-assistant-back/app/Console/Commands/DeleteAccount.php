<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeleteAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:delete
                                        {--account_id= : Номер аккаунта, товары для которого должны быть удалены товары }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаление аккаунт Wildberries или Ozon';

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

        $accountId = $this->option('account_id');

        if (!$accountId) {
            $this->error('Не указан аккаунт!');
            return 0;
        }
        // проверяем какого типа этот аккаунт
        $ozProductCount = OzProduct::where('account_id', $accountId)->withTrashed()->count() +
            OzTemporaryProduct::where('account_id', $accountId)->withTrashed()->count();
        if (!$ozProductCount) {
            $this->alert("Товаров Ozon с account_id = {$accountId} не найдено");
        } else {
            $this->alert("Найдено {$ozProductCount} товаров Ozon и account_id = {$accountId}");
            $this->handleOzon($accountId);
        }
        // Теперь на WB
        $wbProductCount = WbProduct::where('account_id', $accountId)->withTrashed()->count() +
            WbTemporaryProduct::where('account_id', $accountId)->withTrashed()->count();
        if (!$wbProductCount) {
            $this->alert("Товаров Wildberries с account_id = {$accountId} не найдено");
        } else {
            $this->alert("Найдено {$wbProductCount} товаров Wildberries и account_id = {$accountId}");
            $this->handleWildberries($accountId);
        }
        $this->info("\n\n");
        $this->info("Всё закончено!");
        return 0;
    }


    /**
     * Удаление аккаунта Ozon
     * @param $accountId
     */
    private function handleOzon($accountId)
    {

        $bar = $this->output->createProgressBar(OzProduct::where('account_id', $accountId)->withTrashed()->count());
        $bar->start();

        // Disable foreign key constant
        Schema::disableForeignKeyConstraints();

        // table oz_products
        foreach (OzProduct::where('account_id', $accountId)->withTrashed()->get() as $product) {
            try {
                $ids = DB::table('oz_product_change_history')->where('product_id', $product->id)->pluck('id')->toArray();
                DB::table('oz_product_feature_history')->whereIn('history_id', $ids)->delete();
                DB::table('oz_product_feature_error_history')->whereIn('history_id', $ids)->delete();
                DB::table('oz_product_price_change_history')->whereIn('product_history_id', $ids)->delete();
                DB::table('oz_product_change_history')->where('product_id', $product->id)->delete();

                DB::table('oz_products_features')->where('product_id', $product->id)->delete();
                DB::table('oz_trigger_remove_from_sale')->where('product_id', $product->id)->delete();
                DB::table('oz_trigger_change_min_photos')->where('product_id', $product->id)->delete();
                DB::table('oz_trigger_change_min_reviews')->where('product_id', $product->id)->delete();
                DB::table('oz_product_positions_history_graph')->where('product_id', $product->id)->delete();
                DB::table('oz_product_positions_history')->where('product_id', $product->id)->delete();
                $product->forceDelete();
                // table oz_product_user
                $this->deleteDataInProductUserRelatedTable('wb_product_user', $accountId);
            } catch (\Exception $exception) {
                report($exception);
                $this->error($exception->getMessage());
            }
            $bar->advance();
        }

        Schema::enableForeignKeyConstraints();

        $bar->finish();
    }

    /**
     *  Удаление аккаунта Wildberries
     * @param $accountId
     */
    private function handleWildberries($accountId)
    {
        // table wb_products
        WbProduct::where('account_id', $accountId)->withTrashed()->forceDelete();
        // table wb_temporary_products
        WbTemporaryProduct::where('account_id', $accountId)->withTrashed()->forceDelete();
        // table wb_nomenclatures
        WbNomenclature::where('account_id', $accountId)->delete();
        // table wb_product_user
        $this->deleteDataInProductUserRelatedTable('wb_product_user', $accountId);
    }

    /**
     * Delete data in user related table
     * @param $table
     * @param $accountId
     */
    private function deleteDataInProductUserRelatedTable($table, $accountId)
    {
        if (Schema::hasTable($table)) {
            DB::table($table)->where('account_id', $accountId)->delete();
        }
    }
}
