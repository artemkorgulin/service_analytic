<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzOptionFrequenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_option_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('option_value')->nullable(false)->unique()->comment('v2_ozon_feature_options.value - значение справочника.
Из значения справочника удаляются слова из подготовленного словаря');
            $table->integer('option_value_count')->nullable()->default(1)->comment('Сколько раз встречается данная опция в характеристиках товара');
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
        Schema::dropIfExists('oz_option_frequencies');
    }
}
