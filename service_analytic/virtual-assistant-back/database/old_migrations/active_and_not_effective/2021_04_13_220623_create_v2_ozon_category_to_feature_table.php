<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('category_id')->constrained('v2_ozon_categories');
            $table->foreignId('feature_id')->constrained('v2_ozon_features');
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
