<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ProductFeatureHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_product_feature_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('history_id')->constrained('v2_product_change_history');
            $table->foreignId('feature_id')->constrained('v2_ozon_features');
            $table->string('value');
            $table->boolean('is_send')->default(0);
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
        Schema::dropIfExists('v2_product_feature_history');
    }
}
