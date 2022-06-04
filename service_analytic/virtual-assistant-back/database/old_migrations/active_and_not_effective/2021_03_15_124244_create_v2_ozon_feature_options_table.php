<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2OzonFeatureOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_ozon_feature_options', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->unsignedBigInteger('external_id')->unique();
            $table->foreignId('category_id')->constrained('v2_ozon_categories');
            $table->foreignId('feature_id')->constrained('v2_ozon_features');
            $table->boolean('is_deleted')->default(0);
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
        Schema::dropIfExists('v2_ozon_feature_options');
    }
}
