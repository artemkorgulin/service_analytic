<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbTemporaryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_temporary_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->nullable(false)->comment('ID пользователя');
            $table->unsignedBigInteger('account_id')->index()->nullable(false)->comment('ID учетной записи');
            $table->string('card_id')->index()->nullable(false)->comment('ID карточки товара');
            $table->bigInteger('imt_id')->index()->nullable(false)->comment('ID imt карточки товара');
            $table->bigInteger('card_user_id')->nullable()->comment('Id пользователя');
            $table->string('supplier_id')->nullable()->comment('Id поставщика (видимо для WB из 1С)');
            $table->string('imt_supplier_id')->nullable()->comment('ID поставщика по карточке');
            $table->string('title')->index()->nullable(false)->comment('Наименование');
            $table->string('brand')->index()->nullable(false)->comment('Бренд товара');
            $table->json('barcodes')->nullable()->comment('Баркоды продукта');
            $table->string('nmid')->index()->nullable()->comment('nmId продукта');
            $table->string('sku')->index()->nullable()->comment('SKU продукта');
            $table->string('image')->index()->nullable()->comment('Изображение продукта');
            $table->decimal('price')->index()->nullable()->comment('Цена продукта');
            $table->string('object')->index()->nullable()->comment('Тип продукта');
            $table->string('parent')->index()->nullable()->comment('Родительская группа типа продукта');
            $table->string('country_production')->nullable()->comment('Страна производства');
            $table->string('supplier_vendor_code')->nullable()->comment('Код поставщика');
            $table->json('data')->nullable()->comment('Массив в котором храним данные о продукте');
            $table->json('nomenclatures')->nullable()->comment('Номенклатуры продукта');
            $table->string('url')->nullable()->comment('URL продукта');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_temporary_products');
    }
}
