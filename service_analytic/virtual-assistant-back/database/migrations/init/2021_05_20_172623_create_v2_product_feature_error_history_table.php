<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2ProductFeatureErrorHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_product_feature_error_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('history_id');
            $table->unsignedBigInteger('feature_id');

            $table->foreign('feature_id', 'v2_product_feature_error_history_feature_id_foreign')->references('id')->on('v2_ozon_features');
            $table->foreign('history_id', 'v2_product_feature_error_history_history_id_foreign')->references('id')->on('v2_product_change_history');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_product_feature_error_history');
    }
}
