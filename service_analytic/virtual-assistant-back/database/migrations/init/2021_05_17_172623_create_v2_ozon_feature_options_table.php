<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('value', 5000)->nullable();
            $table->unsignedBigInteger('external_id')->unique('v2_ozon_feature_options_external_id_unique');
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
