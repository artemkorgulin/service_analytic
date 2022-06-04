<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzProductTop36sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_product_top36s', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->index()->nullable(false)
                ->comment('Имя файла для парсинга');
            $table->date('parsed_at')->index()->nullable(false)
                ->comment('Дата для парсинга');
            $table->bigInteger('web_category_id')->index()->nullable(false)
                ->comment('WEB ID категории');
            $table->enum('type', ['min', 'max', 'avg'])->index()->nullable(false)
                ->comment('Тип какие параметры выбираем мин / макс или средние');
            $table->decimal('price', 10, 2)->index()->nullable()
                ->comment('Цена');
            $table->integer('review_count')->index()->nullable()
                ->comment('Количество отзывов');
            $table->decimal('rating', 5, 2)->index()->nullable()
                ->comment('Рейтинг');
            $table->integer('photo_count')->index()->nullable()
                ->comment('Количество фото');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_product_top36s');
    }
}
