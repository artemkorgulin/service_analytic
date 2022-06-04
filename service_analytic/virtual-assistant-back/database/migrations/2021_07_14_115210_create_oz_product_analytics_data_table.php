<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzProductAnalyticsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_product_analytics_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->nullable(false)->comment('Ссылка на пользователя');
            $table->unsignedBigInteger('account_id')->index()->nullable(true)->comment('Ссылка аккаунт');
            $table->unsignedBigInteger('product_id')->index()->nullable(false)->comment('Ссылка на id товара');
            $table->string('external_id')->index()->nullable(false)->comment('Ссылка extenal_id товара');
            $table->string('sku')->index()->nullable(false)->comment('Ссылка на SKU товара в Ozon');
            $table->string('name')->index()->nullable(true)->comment('Название товара в ozon');
            $table->date('report_date')->index()->nullable(true)->comment('Дата отчета на какой день');
            $table->integer('hits_view_search')->nullable()->default(0)->comment('показы в поиске и в категории');
            $table->integer('hits_view_pdp')->nullable()->default(0)->comment('показы на карточке товара');
            $table->integer('hits_view')->nullable()->default(0)->comment('всего показов');
            $table->integer('hits_tocart_search')->nullable()->default(0)->comment('в корзину из поиска или категории');
            $table->integer('hits_tocart_pdp')->nullable()->default(0)->comment('в корзину из карточки товара');
            $table->integer('hits_tocart')->nullable()->default(0)->comment('всего добавлено в корзину');
            $table->integer('session_view_search')->nullable()->default(0)->comment('сессии с показом в поиске или в категории');
            $table->integer('session_view_pdp')->nullable()->default(0)->comment('сессии с показом на карточке товара');
            $table->integer('session_view')->nullable()->default(0)->comment('всего сессий');
            $table->integer('conv_tocart_search')->nullable()->default(0)->comment('конверсия в корзину из поиска или категории');
            $table->integer('conv_tocart_pdp')->nullable()->default(0)->comment('конверсия в корзину из карточки товара');
            $table->integer('conv_tocart')->nullable()->default(0)->comment('общая конверсия в корзину');
            $table->integer('revenue')->nullable()->default(0)->comment('заказано на сумму');
            $table->integer('returns')->nullable()->default(0)->comment('возвращено товаров');
            $table->integer('cancellations')->nullable()->default(0)->comment('отменено товаров');
            $table->integer('ordered_units')->nullable()->default(0)->comment('заказано товаров');
            $table->integer('delivered_units')->nullable()->default(0)->comment('доставлено товаров');
            $table->integer('adv_view_pdp')->nullable()->default(0)->comment('показы на карточке товара, спонсорские товары');
            $table->integer('adv_view_search_category')->nullable()->default(0)->comment('показы в поиске и в категории, спонсорские товары');
            $table->integer('adv_view_all')->nullable()->default(0)->comment('показы всего, спонсорские товары');
            $table->integer('adv_sum_all')->nullable()->default(0)->comment('всего расходов на рекламу');
            $table->integer('position_category')->nullable()->default(0)->comment('позиция в поиске и категории');
            $table->integer('postings')->nullable()->default(0)->comment('отправления');
            $table->integer('postings_premium')->nullable()->default(0)->comment('отправления с подпиской Premium');
            $table->timestamps();

            $table->unique(['product_id', 'report_date']);

            $table->foreign('product_id')->on('oz_products')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `oz_product_analytics_data` comment 'Содержит информацию по аналитическим данным получаемым из Ozon скорее всего часть значений лишние, но я храню всё'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_product_analytics_data');
    }
}
