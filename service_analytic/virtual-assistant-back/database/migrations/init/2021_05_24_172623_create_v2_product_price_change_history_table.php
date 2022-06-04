<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2ProductPriceChangeHistoryTable extends Migration
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
            $table->decimal('price', 8, 2);
            $table->decimal('old_price', 8, 2);
            $table->tinyInteger('sent_from_va');
            $table->boolean('is_applied');
            $table->text('errors')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_history_id');
            $table->timestamps();

            $table->foreign('product_history_id', 'v2_product_price_change_history_product_history_id_foreign')->references('id')->on('v2_product_change_history');
            $table->foreign('product_id', 'v2_product_price_change_history_product_id_foreign')->references('id')->on('v2_products');
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
