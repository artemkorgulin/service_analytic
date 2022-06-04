<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2OzonCategoryToFeatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_ozon_category_to_feature', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('feature_id');

            $table->foreign('category_id', 'v2_ozon_category_to_feature_category_id_foreign')->references('id')->on('v2_ozon_categories');
            $table->foreign('feature_id', 'v2_ozon_category_to_feature_feature_id_foreign')->references('id')->on('v2_ozon_features');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_ozon_category_to_feature');
    }
}
