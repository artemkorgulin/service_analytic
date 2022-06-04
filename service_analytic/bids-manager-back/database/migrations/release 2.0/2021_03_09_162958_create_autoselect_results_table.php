<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoselectResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoselect_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autoselect_parameter_id');
            $table->integer('va_request_id');
            $table->string('name');
            $table->dateTime('date');
            $table->integer('popularity');
            $table->integer('cart_add_count');
            $table->integer('avg_cost');

            $table->foreign('autoselect_parameter_id')->references('id')->on('autoselect_parameters')
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
        Schema::dropIfExists('autoselect_results');
    }
}
