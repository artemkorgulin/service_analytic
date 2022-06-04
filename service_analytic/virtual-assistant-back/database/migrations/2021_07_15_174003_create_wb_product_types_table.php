<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbProductTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->nullable(false)->unique()->comment('Название типа продукта');
            $table->json('data')->nullable()->comment('Для хранения данных');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `oz_product_analytics_data` comment 'Таблица для хранениня типов товаров и параметров товаров из WB'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_product_types');
    }
}
