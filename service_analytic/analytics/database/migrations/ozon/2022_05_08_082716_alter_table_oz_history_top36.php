<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzHistoryTop36 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_history_top36', function (Blueprint $table) {
            $table->smallInteger('rating_avg')->comment('Рейтинг из ТОП 36')
                ->change();
            $table->smallInteger('comments_avg')->change()
                ->comment('Среднее количество отзывов из ТОП 36');
            $table->smallInteger('position')->change()
                ->comment('Средняя позиция в категории по ТОП 36');
            $table->smallInteger('position_category')->change()
                ->comment('Средняя позиция в категории');
            $table->smallInteger('position_search')->change()
                ->comment('Средняя позиция в поиске');
            $table->smallInteger('images_avg')->change()
                ->comment('Среднее количество изображений из ТОП 36');
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
            $table->integer('rating_avg')->comment('Рейтинг из ТОП 36')
                ->change();
            $table->integer('comments_avg')->change()
                ->comment('Среднее количество отзывов из ТОП 36');
            $table->integer('position')->change()
                ->comment('Средняя позиция в категории по ТОП 36');
            $table->integer('position_category')->change()
                ->comment('Средняя позиция в категории');
            $table->integer('position_search')->change()
                ->comment('Средняя позиция в поиске')->change();
            $table->integer('images_avg')->change()
                ->comment('Среднее количество изображений из ТОП 36');
        });
    }
}
