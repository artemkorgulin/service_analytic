<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchQueryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_query_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_query_id');
            $table->unsignedBigInteger('ozon_category_id')->nullable();
            $table->unsignedInteger('popularity');
            $table->unsignedInteger('additions_to_cart');
            $table->unsignedInteger('avg_price');
            $table->unsignedInteger('products_count')->nullable();
            $table->unsignedDecimal('rating', 10, 2)->nullable();
            $table->date('parsing_date');
            $table->index(['parsing_date', 'search_query_id'], 'search_query_histories_parsing_date_search_query_id_index');
            $table->index(['search_query_id', 'ozon_category_id', 'parsing_date'], 'search_query_histories_query_on_date');
            $table->timestamps();

            $table->foreign('ozon_category_id', 'search_query_histories_ozon_category_id_foreign')->references('id')->on('ozon_categories')->onUpdate('cascade');
            $table->foreign('search_query_id', 'search_query_histories_search_query_id_foreign')->references('id')->on('search_queries')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_query_histories');
    }
}
