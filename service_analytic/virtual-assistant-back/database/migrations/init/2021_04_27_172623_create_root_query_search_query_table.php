<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRootQuerySearchQueryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('root_query_search_query', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('root_query_id');
            $table->unsignedBigInteger('search_query_id');
            $table->unsignedInteger('popularity')->nullable();
            $table->unsignedInteger('additions_to_cart')->nullable();
            $table->unsignedInteger('avg_price')->nullable();
            $table->unsignedInteger('products_count')->nullable();
            $table->unsignedDecimal('rating', 10, 2)->nullable();
            $table->unsignedDecimal('filtering_ratio', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('root_query_id', 'root_query_search_query_root_query_id_foreign')->references('id')->on('root_queries')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('search_query_id', 'root_query_search_query_search_query_id_foreign')->references('id')->on('search_queries')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('root_query_search_query');
    }
}
