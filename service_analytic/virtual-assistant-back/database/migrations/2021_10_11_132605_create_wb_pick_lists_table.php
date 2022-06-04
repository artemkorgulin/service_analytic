<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbPickListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_pick_lists', function (Blueprint $table) {
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
        Schema::dropIfExists('wb_pick_lists');
    }
}
