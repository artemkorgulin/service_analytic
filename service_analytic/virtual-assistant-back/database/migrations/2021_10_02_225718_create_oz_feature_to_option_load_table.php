<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzFeatureToOptionLoadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Таблица для временного хранения опций во время парсинга озона
        Schema::create('oz_feature_to_option_load', function (Blueprint $table) {
            $table->id();
            $table->integer('option_id')->index();
            $table->string('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_feature_to_option_load');
    }
}
