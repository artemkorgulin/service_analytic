<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbTop20FeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_top20_features', function (Blueprint $table) {
            $table->id();
            $table->string('directory_slug')->index()->nullable(false)->comment('Slug словаря');
            $table->unsignedBigInteger('directory_id')->index()->nullable(false)->comment('ID словаря не очень обязательный но пусть будет');
            $table->string('object')->index()->nullable()->comment('Категория товара (объект) для получения кода ТНВЭД');
            $table->unsignedBigInteger('feature_id')->index()->nullable()->comment('ID характеристики');
            $table->string('feature_title')->index()->nullable()->comment('Наименование характеристики');
            $table->string('title')->index()->nullable(false)->comment('Значение характеристики топ 20');
            $table->integer('popularity')->index()->nullable()->comment('Популярность запроса');
            $table->boolean('has_in_ozon')->index()->nullable()->comment('Есть ли такая характеристика (значение) в Ozon');

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
        Schema::dropIfExists('wb_top20_features');
    }
}
