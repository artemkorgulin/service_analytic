<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOzProductsTableAddFieldPrmiumPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->decimal('premium_price', 8, 2)->after('price')->index()->nullable()->comment('Цена для Ozon c премиальной подпиской');
            $table->decimal('buybox_price', 8, 2)->after('price')->index()->nullable()->comment('Цена для Ozon при покупке коробкой');
            $table->decimal('marketing_price', 8, 2)->after('price')->index()->nullable()->comment('Маркетинговая цена?!');
            $table->decimal('min_ozon_price', 8, 2)->after('price')->index()->nullable()->comment('Маркетинговая цена?!');
            $table->string('vat', 10)->after('min_ozon_price')->default('0.200000')->index()->nullable()->comment('НДС');
            $table->float('volume_weight')->after('vat')->default(0)->nullable()->comment('Как я понимаю это объемный вес');
            $table->string('barcode', 30)->after('sku')->index()->nullable()->comment('Баркод очень важно UPC или EAN');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            //
        });
    }
}
