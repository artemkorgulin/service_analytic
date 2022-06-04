<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzHistoryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_history_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('account_id')->nullable()->comment('Аккаунт пользователя');
            $table->integer('vendor_code');
            $table->string('name');
            $table->integer('subject_id')->comment('Предмет')->nullable();
            $table->integer('category_id')->comment('ID категории / web_id')->nullable();
            $table->date('date');
            $table->integer('position_category')->comment('Позиция в категории');
            $table->integer('position_search')->comment('Позиция в поиске');
            $table->integer('rating')->comment('Рейтинг товара');
            $table->integer('optimization')->comment('Оптимизация');
            $table->integer('comments')->comment('Комментарии');
            $table->integer('escrow')->comment('Защита авторства');
            $table->integer('images')->comment('Количество изображений');
            $table->integer('current_sales')->comment('Текущие продажи');
            $table->integer('id_history_top36')->comment('Позиция из history_top_36');
            $table->string('url')->nullable();
            $table->timestamps();
            $table->index('vendor_code');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_history_products');
    }
}
