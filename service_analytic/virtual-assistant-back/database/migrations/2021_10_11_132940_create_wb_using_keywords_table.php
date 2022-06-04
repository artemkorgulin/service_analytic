<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbUsingKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_using_keywords', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('wb_product_id')->unsigned()->index();
            $table->string('name');
            $table->integer('popularity');
            $table->integer('conv');
            $table->integer('section');
            $table->timestamps();

            $table->foreign('wb_product_id')->on('wb_products')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_using_keywords');
    }
}
