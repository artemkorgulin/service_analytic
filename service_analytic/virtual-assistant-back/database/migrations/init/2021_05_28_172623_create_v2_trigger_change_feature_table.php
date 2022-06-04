<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2TriggerChangeFeatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_trigger_change_feature', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_shown')->default(false);
            $table->timestamps();

            $table->foreign('category_id', 'v2_trigger_change_feature_category_id_foreign')->references('id')->on('v2_ozon_categories');
            $table->foreign('feature_id', 'v2_trigger_change_feature_feature_id_foreign')->references('id')->on('v2_ozon_features');
            $table->foreign('product_id', 'v2_trigger_change_feature_product_id_foreign')->references('id')->on('v2_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_trigger_change_feature');
    }
}
