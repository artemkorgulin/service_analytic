<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacteristicRootQuerySearchQueryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characteristic_root_query_search_query', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('characteristic_id');
            $table->unsignedBigInteger('root_query_search_query_id')->nullable();
            $table->unsignedInteger('rating')->nullable();

            $table->foreign('characteristic_id', 'characteristic_search_query_characteristic_id_foreign')->references('id')->on('characteristics')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('root_query_search_query_id', 'crqsq_root_query_search_query_id_foreign')->references('id')->on('root_query_search_query')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characteristic_root_query_search_query');
    }
}
