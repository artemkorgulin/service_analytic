<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->nullable(false)->unsigned()->index();
            $table->string('title')->nullable(false)->index()->comment('Наименование варианта товара');
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
            $table->timestamps();

            $table->foreign('product_id')->on('products')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `variants` comment 'Таблица вариантов товара - например для различных цветов или размеров (как пример)'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variants');
    }
}
