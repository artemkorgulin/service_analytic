<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewTariffsAndServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->boolean('visible');
            $table->integer('price')
                  ->comment('Цена в копейках. Нужно ещё поделить на 100');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->boolean('visible');
            $table->timestamps();
        });

        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services');

            $table->integer('min_amount')
                  ->comment('Объём заказа минимальный для вступления цены в силу');
            $table->integer('price_per_item')
                  ->comment('Цена в копейках. Нужно ещё поделить на 100');

            $table->timestamps();
        });

        Schema::create('service_tariff', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services');

            $table->unsignedBigInteger('tariff_id');
            $table->foreign('tariff_id')
                  ->references('id')
                  ->on('tariffs');

            $table->integer('amount')
                  ->comment('Объём, включённый в тариф');

            $table->timestamps();
        });


        Schema::create('order_service', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services');

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders');

            $table->integer('ordered_amount')
                  ->comment('Заказанное количество');

            $table->integer('total_price')
                  ->comment('Общая цена за услугу. Нужно ещё поделить на 100');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->datetime('canceled_at')->nullable();

            $table->unsignedBigInteger('tariff_id')->nullable();
            $table->foreign('tariff_id')
                  ->references('id')
                  ->on('tariffs');

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
            $table->dropColumn('canceled_at');

            $table->dropForeign('orders_tariff_id_foreign');
            $table->dropForeign('orders_company_id_foreign');
            $table->dropColumn('tariff_id');
            $table->dropColumn('company_id');
        });
        Schema::dropIfExists('order_service');
        Schema::dropIfExists('service_tariff');
        Schema::dropIfExists('service_prices');
        Schema::dropIfExists('services');
        Schema::dropIfExists('tariffs');
    }
}
