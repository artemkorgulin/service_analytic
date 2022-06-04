<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_collection', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index()->nullable(false)->comment('Ссылка на таблицу продукты');
            $table->unsignedBigInteger('collection_id')->index()->nullable(false)->comment('Cсылка на таблицу коллекций');

            $table->unique(['product_id', 'collection_id']);

            $table->foreign('product_id')->on('products')->references('id')->onDelete('cascade');
            $table->foreign('collection_id')->on('categories')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `product_collection` comment 'Связующая таблица между товарами и коллекциями'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_collection');
    }
}
