<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attribute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index()->nullable(false)->comment('Ссылка на таблицу продукты');
            $table->unsignedBigInteger('attribute_id')->index()->nullable(false)->comment('Ссылка на таблицу аттрибутов');

            $table->unique(['product_id', 'attribute_id']);

            $table->foreign('product_id')->on('products')->references('id')->onDelete('cascade');
            $table->foreign('attribute_id')->on('attributes')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `product_attribute` comment 'Связующая таблица между товарами и их аттрибутами'");



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attribute');
    }
}
