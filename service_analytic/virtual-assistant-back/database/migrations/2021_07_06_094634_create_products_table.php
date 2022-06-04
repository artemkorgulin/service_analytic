<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false)->index()->comment('Id пользователя');
            $table->unsignedBigInteger('account_id')->nullable(false)->index()->comment('Id аккаунта');
            $table->unsignedBigInteger('type_id')->nullable(true)->index()->comment('Тип продукта. Не получается для этих целей использовать категории как так один товар может быть в нескольких категориях и категории и будут разными в разных аккаунтах');
            $table->unsignedBigInteger('brand_id')->nullable(true)->index()->comment('Бренд (торговая марка) продукта');
            $table->string('title')->nullable(false)->index()->comment('Наименование товара');
            $table->string('sku')->nullable(false)->index()->comment('SKU или артикул товара');
            $table->string('sku_manufacturer')->index()->comment('SKU или артикул товара от производителя');
            $table->string('barcode')->index()->comment('Штрихкод (UPC/EAN-128; EAN-13)');
            $table->string('guid')->index()->comment('Идентификатор в 1С');
            $table->text('small_description')->nullable()->comment('Короткое описание');
            $table->text('description')->nullable()->comment('Длинное описание');
            $table->string('meta_title')->nullable()->comment('Мета название');
            $table->string('meta_keywords')->nullable()->comment('Мета key-words');
            $table->text('meta_description')->nullable()->comment('Мета название');
            $table->decimal('retail_price',10, 2)->default(0)->comment('Розничная цена (правильнее все держать в копейках)');
            $table->enum('retail_price_currency', ['RUB', 'USD', 'EUR'])->default('RUB')->comment('Розничная цена код валюты');
            $table->decimal('sale_price',10, 2)->default(0)->comment('Цена по распродаже');
            $table->enum('sale_price_currency', ['RUB', 'USD', 'EUR'])->default('RUB')->comment('Цена по распродаже валюта');
            $table->decimal('whosale_price',10, 2)->default(0)->comment('Оптовая цена');
            $table->enum('whosale_price_currency', ['RUB', 'USD', 'EUR'])->default('RUB')->comment('Оптовая цена валюта');
            $table->decimal('weight', 8, 3)->default(0)->comment('Масса товара');
            $table->enum('weight_units', [ 'kg', 'g', 'oz', 'lb'])->default('kg')->comment('Масса товара единица измерения');
            $table->decimal('length', 8, 3)->default(0)->comment('Длина товара - габарит упаковки');
            $table->decimal('width', 8, 3)->default(0)->comment('Ширина товара - габарит упаковки');
            $table->decimal('height', 8, 3)->default(0)->comment('Высота товара - габарит упаковки');
            $table->enum('length_units', [ 'm', 'mm', 'in', 'ft'])->default('m')->comment('Мера длины');
            $table->decimal('volume', 10, 3)->default(0)->comment('Объем товара в метрах кубических');
            $table->boolean('shippable')->default(true)->comment('Товар доставляемый');
            $table->boolean('available')->default(true)->comment('Товар доступен');
            $table->timestamps();

            $table->unique(['account_id', 'sku']); // уникальное сочетание для SKU и ID аккаунта

            $table->foreign('type_id')->on('types')->references('id');
            $table->foreign('brand_id')->on('brands')->references('id');
        });

        \DB::statement("ALTER TABLE `categories` comment 'Основная таблица товаров для компании - это главная таблица из которой уже распределяются товары по marketplaces'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
