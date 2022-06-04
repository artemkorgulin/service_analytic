<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbProductWbNomenclaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('wb_product_nomenclatures');

        Schema::create('wb_product_nomenclatures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('wb_product_id')->unsigned()->nullable(false)->index();
            $table->bigInteger('wb_nomenclature_id')->unsigned()->nullable(false)->index();

            $table->foreign('wb_product_id')->on('wb_products')->references('id')->onDelete('cascade');
            $table->foreign('wb_nomenclature_id')->on('wb_nomenclatures')->references('id')->onDelete('cascade');

            $table->unique(['wb_product_id', 'wb_nomenclature_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_product_nomenclatures');
    }
}
