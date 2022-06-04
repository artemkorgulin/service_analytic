<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzTemporaryProductsAddManyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->string('title')->index()->nullable()->after('account_id')->comment('Наименование');
            $table->string('brand')->index()->nullable()->after('title')->comment('Бренд товара');
            $table->string('barcode')->index()->nullable()->after('brand')->comment('Баркод продукта');
            $table->string('offer_id')->index()->nullable()->after('barcode')->comment('Номер товарного предложения продукта');
            $table->unsignedBigInteger('category_id')->index()->nullable()->after('offer_id')->comment('Категория продукта ID');
            $table->string('category')->index()->nullable()->after('category_id')->comment('Категория продукта наименование');
            $table->json('image')->nullable()->after('category')->comment('Изображения продукта');
            $table->json('images')->nullable()->after('image')->comment('Изображения продукта');
            $table->json('descriptions')->nullable()->after('images')->comment('Описания продукта');
            $table->decimal('vat', 10, 6)->default(0.200000)->after('descriptions')->comment('НДС %');

            $table->decimal('price', 10,2)->index()->nullable()->after('vat')->comment('Цена продукта');
            $table->decimal('min_ozon_price', 10,2)->nullable()->after('price')->comment('Цена продукта');
            $table->decimal('buybox_price', 10,2)->nullable()->after('min_ozon_price')->comment('Цена продукта');
            $table->decimal('premium_price', 10,2)->nullable()->after('buybox_price')->comment('Цена продукта');
            $table->decimal('recommended_price', 10,2)->nullable()->after('premium_price')->comment('Цена продукта');
            $table->decimal('old_price', 10,2)->nullable()->after('recommended_price')->comment('Цена продукта');

            $table->decimal('rating',  8,2)->index()->nullable()->after('old_price')->comment('Рейтинг');
            $table->integer('count_reviews')->index()->nullable()->default(0)->after('old_price')->comment('Количество комментариев');
            $table->decimal('optimization',  6,2)->index()->default(0.00)->after('rating')->comment('Рейтинг');

            $table->json('data')->nullable()->after('optimization')->comment('Массив в котором храним данные о продукте');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            //
        });
    }
}
