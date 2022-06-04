<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablesTarifPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('old_tariffs', function (Blueprint $table) {
            //$table->foreign('price_id')->references('id')->on('tariff_prices');
            //$table->dropForeign('tariffs_price_id_foreign');
        });

        DB::table('tariff_prices')->truncate();

        Schema::table('tariff_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('tariff_id',);
        });

//        Schema::table('tariffs', function (Blueprint $table) {
//            $table->foreign('price_id')->references('id')->on('tariff_prices');
//        });

        DB::table('tariff_prices')->insert([
            ['tariff_id' => 1, 'currency' => 'RUB', 'price' =>  '990', 'vat_code' => '1'],
            ['tariff_id' => 2, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 3, 'currency' => 'RUB', 'price' =>  '1990', 'vat_code' => ''],
            ['tariff_id' => 4, 'currency' => 'RUB', 'price' =>  '3990', 'vat_code' => '1'],
            ['tariff_id' => 5, 'currency' => 'RUB', 'price' =>  '2490', 'vat_code' => '1'],
            ['tariff_id' => 6, 'currency' => 'RUB', 'price' =>  '4490', 'vat_code' => '1'],
            ['tariff_id' => 7, 'currency' => 'RUB', 'price' =>  '6990', 'vat_code' => '1'],
            ['tariff_id' => 8, 'currency' => 'RUB', 'price' =>  '6990', 'vat_code' => '1'],
            ['tariff_id' => 9, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 10, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 11, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 12, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 13, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tariff_prices')->truncate();
    }
}
