<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2ProductsFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_products_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('feature_id', 'v2_products_features_feature_id_foreign')->references('id')->on('v2_ozon_features');
            $table->foreign('option_id', 'v2_products_features_option_id_foreign')->references('id')->on('v2_ozon_feature_options');
            $table->foreign('product_id', 'v2_products_features_product_id_foreign')->references('id')->on('v2_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_products_features');
    }
}
