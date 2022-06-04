<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2OzonFeatureToOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_ozon_feature_to_option', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_id');
            $table->unsignedBigInteger('feature_id');
            $table->boolean('is_deleted')->default(0);
            $table->unique(['feature_id', 'option_id'], 'v2_ozon_feature_to_option_feature_id_option_id_unique');

            $table->foreign('feature_id', 'v2_ozon_feature_to_option_feature_id_foreign')->references('id')->on('v2_ozon_features');
            $table->foreign('option_id', 'v2_ozon_feature_to_option_option_id_foreign')->references('id')->on('v2_ozon_feature_options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_ozon_feature_to_option');
    }
}
