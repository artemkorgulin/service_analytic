<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsAddNomenclaturesAndImageFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            $table->decimal('price')->nullable()->after('brand')->comment('Цена товара');
            $table->json('nomenclatures')->nullable()->after('data')->comment('Данные по номенклатурам товара');
            $table->string('image')->index()->nullable()->after('brand')->comment('Ссылка на изображение');

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
