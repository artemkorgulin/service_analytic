<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNullableFieldInOzProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->unsignedBigInteger('external_id')->index()->nullable()->comment('ID товара Ozon, у нового товара он отсутствует!')->change();
            $table->string('url')->index()->nullable()->comment('URL товара в Ozon, у нового товара он отсутствует!')->change();
            $table->string('sku')->index()->nullable()->comment('SKU озона, именно оно попадает в URL')->change();
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
