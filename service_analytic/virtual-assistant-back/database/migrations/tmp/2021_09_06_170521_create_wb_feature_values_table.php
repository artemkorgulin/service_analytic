<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbFeatureValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_feature_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id')->index()->comment('Ссылка на параметр');
            $table->unsignedBigInteger('count')->index()->nullable()->comment('Количественное значение в товаре');
            $table->string('units')->index()->nullable()->comment('Единицы измерения');
            $table->string('value')->index()->nullable()->comment('Значение');
            $table->unsignedInteger('ordering')->nullable()->default(0)->comment('Поле для сортировки (обычно не используется на будущее)');
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
        Schema::dropIfExists('wb_feature_values');
    }
}
