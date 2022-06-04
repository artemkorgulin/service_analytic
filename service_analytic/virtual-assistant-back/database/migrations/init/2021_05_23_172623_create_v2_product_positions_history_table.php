<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2ProductPositionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_product_positions_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('position')->nullable();
            $table->boolean('is_trigger')->default(0);
            $table->string('category')->nullable();
            $table->date('date')->default('2021-05-14');
            $table->timestamps();

            $table->foreign('product_id', 'v2_product_positions_history_product_id_foreign')->references('id')->on('v2_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_product_positions_history');
    }
}
