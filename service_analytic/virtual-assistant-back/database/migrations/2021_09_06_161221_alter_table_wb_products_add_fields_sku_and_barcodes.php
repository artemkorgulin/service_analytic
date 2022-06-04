<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsAddFieldsSkuAndBarcodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            $table->string('sku')->nullable()->index()->after('brand')->comment('Содержит nmId номенклатуры (первой)');
            $table->string('nmid')->nullable()->index()->after('brand')->comment('Содержит nmId номенклатуры (первой)');
            $table->json('barcodes')->nullable()->after('brand')->comment('Содержит баркоды товаров');
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
