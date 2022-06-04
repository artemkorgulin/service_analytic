<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('status_id');
            $table->unsignedDouble('budget');
            $table->unsignedBigInteger('good_id')->nullable();
            $table->unsignedBigInteger('statistics_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

//            $table->foreign('status_id')->references('id')->on('status');
//            $table->foreign('good_id')->references('id')->on('good');
//            $table->foreign('statistics_id')->references('id')->on('campaign_statistics');
//            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign');
    }
}
