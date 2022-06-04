<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable(false)->comment('Ссылка на продукт');
            $table->unsignedBigInteger('image_id')->nullable(false)->comment('Ссылка на изображение');
            $table->string('title')->nullable()->comment('Наименование для картинки');
            $table->string('alt')->nullable()->comment('Alt для картинки');
            $table->integer('sorting')->default(0)->nullable()->index()->comment('Поле для сортировки ');

            $table->foreign('product_id')->on('products')->references('id')->onDelete('cascade');
            $table->foreign('image_id')->on('images')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `product_image` comment 'Связующая таблица между товарами и изображениями'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_image');
    }
}
