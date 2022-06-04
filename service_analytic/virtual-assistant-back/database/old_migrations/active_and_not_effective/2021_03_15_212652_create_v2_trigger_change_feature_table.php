<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('category_id')->constrained('v2_ozon_categories');
            $table->foreignId('feature_id')->constrained('v2_ozon_features');
            $table->foreignId('product_id')->constrained('v2_products');
            $table->boolean('is_shown')->default(0);
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
        Schema::dropIfExists('v2_trigger_change_feature');
    }
}
