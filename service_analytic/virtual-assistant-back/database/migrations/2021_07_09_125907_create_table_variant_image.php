<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableVariantImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variant_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->nullable(false)->comment('Ссылка на вариант');
            $table->unsignedBigInteger('image_id')->nullable(false)->comment('Ссылка на изображение');
            $table->string('title')->nullable()->comment('Наименование для картинки');
            $table->string('alt')->nullable()->comment('Alt для картинки');
            $table->integer('sorting')->default(0)->nullable()->index()->comment('Поле для сортировки ');

            $table->foreign('variant_id')->on('products')->references('id')->onDelete('cascade');
            $table->foreign('image_id')->on('images')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `variant_image` comment 'Связующая таблица между вариантами товаров и изображениями'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variant_image');
    }
}
