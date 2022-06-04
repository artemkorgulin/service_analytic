<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPriceChangeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_product_price_change_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('price');
            $table->boolean('sent_from_va');
            $table->boolean('is_applied');
            $table->text('errors')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('v2_products');
            $table->unsignedBigInteger('product_history_id');
            $table->foreign('product_history_id')->references('id')->on('v2_product_change_history');
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
        Schema::dropIfExists('v2_product_price_change_history');
    }
}
