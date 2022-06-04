<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzHistoryProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_history_products', function (Blueprint $table) {
            $table->smallInteger('position_category')->change()
                ->comment('Позиция в категории');
            $table->smallInteger('position_search')->change()
                ->comment('Позиция в поиске');
            $table->smallInteger('rating')->comment('Рейтинг товара')->change();
            $table->smallInteger('optimization')->comment('Оптимизация')
                ->change();
            $table->smallInteger('comments')->comment('Комментарии')->change();
            $table->smallInteger('escrow')->comment('Защита авторства')
                ->change();
            $table->smallInteger('images')->comment('Количество изображений')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_history_top36', function (Blueprint $table) {
            $table->integer('position_category')->change()
                ->comment('Позиция в категории');
            $table->integer('position_search')->comment('Позиция в поиске')
                ->change();
            $table->integer('rating')->comment('Рейтинг товара')->change();
            $table->integer('optimization')->comment('Оптимизация')->change();
            $table->integer('comments')->comment('Комментарии')->change();
            $table->integer('escrow')->comment('Защита авторства')->change();
            $table->integer('images')->comment('Количество изображений')
                ->change();
        });
    }
}
