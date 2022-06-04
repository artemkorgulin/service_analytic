<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('good_id');
            $table->date('date');
            $table->double('consumption');
            $table->integer('views')->nullable();
            $table->integer('clicks')->nullable();
            $table->double('ctr')->default(0);
            $table->double('avg_1000_views_price')->nullable();
            $table->double('avg_click_price')->nullable();
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
        Schema::dropIfExists('good_statistics');
    }
}
