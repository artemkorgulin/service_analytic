<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffsPromoPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)
            ->update( ['tariff_price' => 2990, 'sku' => 100, 'period' => 1, 'visible' => 1]);

        DB::table('tariff_prices')->where('tariff_id' ,'=',  1)
            ->update( ['price' => 2990]);

        DB::table('tariffs')->whereIn('tariff_id' ,[2,3,4,5,6,7,8,14,15,16,17,18,19])
            ->update( ['visible' => 0, 'active' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)
            ->update( ['tariff_price' => 2999, 'sku' => 100, 'period' => 1, 'visible' => 0]);

        DB::table('tariff_prices')->where('tariff_id' ,'=',  1)
            ->update( ['price' => 2999]);

        DB::table('tariffs')->whereIn('tariff_id' ,[2,3,4,5,6,7,8,14,15,16,17,18,19])
            ->update( ['visible' => 1, 'active' => 0]);
    }
}
