<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffsFromPromo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)
            ->update( ['tariff_price' => 2999, 'sku' => 100, 'period' => 1]);

        DB::table('tariff_prices')->where('tariff_id' ,'=',  1)
            ->update( ['price' => 2999]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)
            ->update( ['tariff_price' => 990, 'sku' => 30, 'period' => 0]);

        DB::table('tariff_prices')->where('tariff_id' ,'=',  1)
            ->update( ['price' => 990]);
    }
}
