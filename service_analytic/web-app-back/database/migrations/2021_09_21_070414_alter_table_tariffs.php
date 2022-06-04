<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariff_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tariff_type_id')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tariffs');
        Schema::enableForeignKeyConstraints();

        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tariff_id')->unique();
            $table->string('name');
            $table->string('description');
            $table->unsignedBigInteger('price_id');
            $table->double('price_tariff_id')->nullable();
            //$table->foreign('price_tariff_id')->references('tariff_id')->on('tariff_prices');
            $table->unsignedInteger('period');
            $table->string('payment_subject')->nullable();
            $table->string('payment_mode')->nullable();
            $table->integer('sku')->nullable()->comment('Количество SKU');
            $table->double('discount')->nullable()->comment('Скидка при совокупном использовании(все включено)');
            $table->tinyInteger('visible')->default(0);
            $table->tinyInteger('active')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('tariffs')->insert([
            ['tariff_id' => 1, 'price_id'=> 1, 'price_tariff_id' => 990, 'name' => 'Промо', 'sku' => 30, 'description' => 'Промо', 'visible' => 1, 'active' => 1],
            // Бесплатныей тариф
            ['tariff_id' => 2, 'price_id'=> 2, 'price_tariff_id' => 0, 'name' => 'Оптимизация карточек товара', 'sku' => 3, 'description' => 'Оптимизация контента карточек товара, отслеживание рейтинга и динамики позиций в категории 3 SKU', 'visible' => 1, 'active' => 1],
            ['tariff_id' => 3, 'price_id'=> 3, 'price_tariff_id' => 1990, 'name' => 'Оптимизация карточек товара', 'sku' => 30, 'description' => 'Оптимизация контента карточек товара, отслеживание рейтинга и динамики позиций в категории 30 SKU', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 4, 'price_id'=> 4, 'price_tariff_id' => 3990, 'name' => 'Оптимизация карточек товара', 'sku' => 100, 'description' => 'Оптимизация контента карточек товара, отслеживание рейтинга и динамики позиций в категории 100 SKU', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 5, 'price_id'=> 5, 'price_tariff_id' => 2490, 'name' => 'Управление рекламными кампаниями', 'sku' => 30, 'description' => 'Просмотр и управление рекламными компаниями, стратегия и аналитика 30  SKU', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 6, 'price_id'=> 6, 'price_tariff_id' => 4490, 'name' => 'Управление рекламными кампаниями', 'sku' => 100, 'description' => 'Просмотр и управление рекламными компаниями, стратегия и аналитика 100 SKU', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 7, 'price_id'=> 7, 'price_tariff_id' => 6990, 'name' => 'Аналитика маркетплейсов', 'sku' => 30, 'description' => 'Аналитика по маркетплейсам(отчеты, сравнения)', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 8, 'price_id'=> 8, 'price_tariff_id' => 6990, 'name' => 'Аналитика маркетплейсов', 'sku' => 100, 'description' => 'Аналитика по маркетплейсам(отчеты, сравнения)', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 9, 'price_id'=> 9, 'price_tariff_id' => null, 'name' => 'Поисковая оптимизация', 'sku' => 30, 'description' => 'Поисковая оптимизация - оптимизация карточек товаров для улучшения видимости 30 SKU', 'visible' => 0, 'active' => 0],
            ['tariff_id' => 10, 'price_id'=> 10, 'price_tariff_id' => null, 'name' => 'Поисковая оптимизация', 'sku' => 100, 'description' => 'Поисковая оптимизация - оптимизация карточек товаров для улучшения видимости 100 SKU', 'visible' => 0, 'active' => 0],
            ['tariff_id' => 11, 'price_id'=> 11, 'price_tariff_id' => null, 'name' => 'Мониторинг цен', 'sku' => 30, 'description' => 'Мониторинг цен 30', 'visible' => 0, 'active' => 0],
            ['tariff_id' => 12, 'price_id'=> 13, 'price_tariff_id' => null, 'name' => 'Мониторинг цен', 'sku' => 100, 'description' => 'Мониторинг цен 100', 'visible' => 0, 'active' => 0],
            ['tariff_id' => 13, 'price_id'=> 13, 'price_tariff_id' => null, 'name' => 'Индивидуальный', 'sku' => null, 'description' => 'Индивидуальный', 'visible' => 0, 'active' => 0],
            // Бесплатные тарифы
            ['tariff_id' => 14, 'price_id'=> 14, 'price_tariff_id' => 0, 'name' => 'Управление рекламными кампаниями', 'sku' => 3, 'description' => 'Просмотр и управление рекламными компаниями, стратегия и аналитика 3 SKU', 'visible' => 1, 'active' => 1],
            ['tariff_id' => 15, 'price_id'=> 15, 'price_tariff_id' => 0, 'name' => 'Аналитика маркетплейсов', 'sku' => 3, 'description' => 'Аналитика по маркетплейсам(отчеты, сравнения)', 'visible' => 1, 'active' => 1],
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tariff_type');
        Schema::dropIfExists('tariffs');
    }
}
