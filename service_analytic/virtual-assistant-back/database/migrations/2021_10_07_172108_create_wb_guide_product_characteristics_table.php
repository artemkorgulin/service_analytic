<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbGuideProductCharacteristicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_guide_product_characteristics', function (Blueprint $table) {
            $table->id();
            //$table->integer('characteristic_id')->index();
            $table->unsignedBigInteger('wb_category_id')->index()->comment('Категория Wilberrries (ссылка на код товара)');   //category->web_id  8175
            $table->string('characteristic_name'); //пишем category_subject or brand or purpose именно, не значение
            //$table->string('characteristic_type')->nullable();
            $table->boolean('is_require')->nullable();  //где взять? пока нигде
            $table->integer('use_frequency')->nullable();
            $table->integer('output_position')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wb_category_id')->on('wb_categories')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_guide_product_characteristics');
    }
}
