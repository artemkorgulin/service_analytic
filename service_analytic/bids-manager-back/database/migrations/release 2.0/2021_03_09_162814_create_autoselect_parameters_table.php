<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAutoselectParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoselect_parameters', function (Blueprint $table) {
            $table->id();
            $table->dateTime('request_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreignId('group_id')->nullable();
            $table->foreignId('campaign_good_id');
            $table->string('keyword');
            $table->foreignId('category_id')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');

            $table->foreign('group_id')->references('id')->on('groups')
                ->onUpdate('cascade');
            $table->foreign('campaign_good_id')->references('id')->on('campaign_goods')
                ->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('categories')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoselect_parameters');
    }
}
