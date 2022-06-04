<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('sku')->unique('sku');
            $table->string('category1', 128)->default('');
            $table->string('category2', 128)->default('');
            $table->string('category3', 128)->default('');
            $table->string('category4', 128)->default('');
            $table->string('name', 255);
            $table->string('brand', 255);
            $table->enum('shipping', ['FBO', 'FBS']);
            $table->unsignedInteger('price');
            $table->boolean('discounted');
            $table->unsignedInteger('review_count');
            $table->unsignedDecimal('rating', 2, 1);
            $table->boolean('rich_content');
            $table->string('seller', 255);
            $table->unsignedMediumInteger('desc_len');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
