<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeysInWbProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            try {
                $table->dropForeign('wb_products_account_id_foreign');
            } catch (\Exception $e) {
                print $e->getMessage();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            //
        });
    }
}
