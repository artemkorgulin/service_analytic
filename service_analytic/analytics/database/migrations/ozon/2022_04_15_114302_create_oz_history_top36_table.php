<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzHistoryTop36Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_history_top36', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_code');
            $table->integer('category_id')->comment('ID категории');
            $table->integer('subject_id')->comment('тип товара');
            $table->date('date')->default(Carbon::now()->format('Y-m-d'))->comment('Дата');
            $table->integer('rating_avg')->comment('Рейтинг из ТОП 36');
            $table->integer('comments_avg')->comment('Среднее количество отзывов из ТОП 36');
            $table->integer('position')->comment('Средняя позиция в категории по ТОП 36');
            $table->integer('position_category')->comment('Средняя позиция в категории');
            $table->integer('position_search')->comment('Средняя позиция в поиске');
            $table->integer('images_avg')->comment('Среднее количество изображений из ТОП 36');
            $table->integer('sale_avg')->comment('Средние продажи из ТОП 36');
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
        Schema::dropIfExists('oz_history_top36s');
    }
}
