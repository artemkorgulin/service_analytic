<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyword_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('keyword_id');
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
        Schema::dropIfExists('keyword_statistics');
    }
}
