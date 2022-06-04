<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2TriggerChangeMinReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_trigger_change_min_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('web_category_id');
            $table->integer('min_reviews');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('product_id', 'v2_trigger_change_min_reviews_product_id_foreign')->references('id')->on('v2_products');
            $table->foreign('web_category_id', 'v2_trigger_change_min_reviews_web_category_id_foreign')->references('id')->on('v2_web_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_trigger_change_min_reviews');
    }
}
