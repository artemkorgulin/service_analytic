<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveDataFromTemporaryProductsToNewTableRemoveOldData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        $this->addAccountClientIdToProductTable('oz_temporary_products');
        $this->addAccountClientIdToProductTable('wb_temporary_products');
        $this->insertDataToUserOzProduct();
        $this->insertDataToUserWbProduct();
        $this->purgeAllDeletedAtProducts();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->purgeDataToUserOzAndWbProduct();
    }

    /**
     * Update use
     * @return void
     */
    private function addAccountClientIdToProductTable($table)
    {
        foreach (DB::table($table)->select('account_id')->groupBy('account_id')->pluck('account_id') as $accountId) {
            $account = $this->getAccountFromWABApp($accountId);
            if (!$account || !isset($account['platform_client_id'])) {
                continue;
            }
            print "Insert data for account {$account['platform_client_id']} in table {$table}\n";
            DB::table($table)
                ->where('account_id', $accountId)
                ->update(['account_client_id' => $account['platform_client_id']]);
        }
    }

    /**
     * Insert data to oz_product_user use select
     * @return void
     */
    private function insertDataToUserOzProduct()
    {
        print "Truncate data in oz_product_user table\n";
        DB::table('oz_product_user')->truncate();
        print "Delete all data with null values in fields account_id, user_id \n";
        DB::table('oz_products')->where(function ($query) {
            $query->whereNull('account_id')
                ->orWhereNull('user_id');
        })->delete();
        print "Insert data in oz_product_user table\n";
        $select = DB::table('oz_products')->select(['user_id', 'account_id', 'external_id', 'is_active'])
            ->whereNull('deleted_at');
        DB::table('oz_product_user')
            ->insertUsing(['user_id', 'account_id', 'external_id', 'is_active'], $select);
    }

    /**
     * Insert data to wb_product_user use select
     * @return void
     */
    private function insertDataToUserWbProduct()
    {
        print "Truncate data in wb_product_user table\n";
        DB::table('wb_product_user')->truncate();
        print "Delete all data with null values in fields account_id, user_id \n";
        DB::table('wb_products')->where(function ($query) {
            $query->whereNull('account_id')
                ->orWhereNull('user_id');
        })->delete();
        print "Insert data in wb_product_user table\n";
        $select = DB::table('wb_products')->select(['user_id', 'account_id', 'imt_id', 'is_active'])
            ->whereNull('deleted_at');
        DB::table('wb_product_user')
            ->insertUsing(['user_id', 'account_id', 'imt_id', 'is_active'], $select);
    }

    /**
     * Truncate all data from wb_product_user and oz_product_user
     */
    private function purgeDataToUserOzAndWbProduct()
    {
        print "Truncate data in wb_product_user table\n";
        DB::table('wb_product_user')->truncate();
        print "Truncate data in oz_product_user table\n";
        DB::table('oz_product_user')->truncate();
    }


    /**
     * Delete duplicates in tables
     */
    private function purgeAllDeletedAtProducts()
    {
        DB::table('oz_temporary_products')->whereNotNull('deleted_at')->delete();
        print("Delete deleted in oz_temporary_products table\n");
        DB::table('oz_products')->whereNotNull('deleted_at')->delete();
        print("Delete deleted in oz_products table\n");
        DB::table('wb_temporary_products')->whereNotNull('deleted_at')->delete();
        print("Delete deleted in wb_temporary_products table\n");
        DB::table('wb_products')->whereNotNull('deleted_at')->delete();
        print("Delete deleted in oz_products table\n");

        DB::delete('DELETE wtp FROM wb_temporary_products wtp
            INNER JOIN
                (SELECT imt_id, MAX(id) AS max_id
                FROM wb_temporary_products wtpt
                GROUP BY imt_id) wtpt
            ON wtp.id <> wtpt.max_id AND wtp.imt_id = wtpt.imt_id'
        );
        print("Delete duplicates in wb_temporary_products table\n");

        DB::delete('DELETE wp FROM wb_products wp
            INNER JOIN
                (SELECT imt_id, MAX(id) AS max_id
                FROM wb_products wpt
                GROUP BY imt_id) wpt
            ON wp.id <> wpt.max_id AND wp.imt_id = wpt.imt_id'
        );
        print("Delete duplicates in wb_products table\n");

        DB::delete('DELETE otp FROM oz_temporary_products otp
            INNER JOIN
                (SELECT external_id, MAX(id) AS max_id
                FROM oz_temporary_products otpt
                GROUP BY external_id) otpt
            ON otp.id <> otpt.max_id AND otp.external_id = otpt.external_id'
        );
        print("Delete duplicates in oz_temporary_products table\n");

        DB::delete('DELETE op FROM oz_products op
            INNER JOIN
                (SELECT external_id, MAX(id) AS max_id
                FROM oz_products opt
                GROUP BY external_id) opt
            ON op.id <> opt.max_id AND op.external_id = opt.external_id'
        );
        print("Delete duplicates in oz_products table\n\n");

        print("All done\n");
    }

    /**
     * Return account from web app back
     * @param $accountId
     */
    public function getAccountFromWABApp($accountId)
    {
        return (array)DB::connection('wab')->table('accounts')->select('*')->where('id', $accountId)->first();
    }
}
