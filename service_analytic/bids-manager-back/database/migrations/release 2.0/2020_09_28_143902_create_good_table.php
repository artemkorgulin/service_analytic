<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('status_id');
            $table->integer('price');
            $table->unsignedBigInteger('keyword_id')->nullable();
            $table->unsignedBigInteger('statistics_id');
            $table->timestamps();

//            $table->foreign('status_id')->references('id')->on('status');
//            $table->foreign('keyword_id')->references('id')->on('keyword');
//            $table->foreign('statistics_id')->references('id')->on('good_statistics');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good');
    }
}
