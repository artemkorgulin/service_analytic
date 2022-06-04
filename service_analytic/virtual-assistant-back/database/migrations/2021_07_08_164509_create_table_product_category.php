<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index()->nullable(false)->comment('Ссылка на продукт');
            $table->unsignedBigInteger('category_id')->index()->nullable(false)->comment('Ссылка на категорию');

            $table->unique(['product_id', 'category_id']);

            $table->foreign('product_id')->on('products')->references('id')->onDelete('cascade');
            $table->foreign('category_id')->on('categories')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `product_category` comment 'Связующая таблица между товарами и их категориями'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_category');
    }
}
