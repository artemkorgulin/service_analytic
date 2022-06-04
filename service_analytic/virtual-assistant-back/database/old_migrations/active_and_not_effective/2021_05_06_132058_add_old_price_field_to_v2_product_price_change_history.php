<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOldPriceFieldToV2ProductPriceChangeHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_product_price_change_history', function (Blueprint $table) {
            $table->decimal('old_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_product_price_change_history', function (Blueprint $table) {
            $table->dropColumn('old_price');
        });
    }
}
