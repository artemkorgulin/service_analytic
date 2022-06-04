<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('root_queries', function(Blueprint $table) {
            $table->index(['name']);
            $table->index(['name', 'ozon_category_id']);
        });

        Schema::table('search_queries', function(Blueprint $table) {
            $table->index(['name', 'is_negative']);
        });

        Schema::table('search_query_histories', function(Blueprint $table) {
            $table->index(['search_query_id', 'ozon_category_id', 'parsing_date'], 'search_query_histories_query_on_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('root_queries', function(Blueprint $table) {
            $table->dropIndex('root_queries_name_index');
            $table->dropIndex('root_queries_name_ozon_category_id_index');
        });

        Schema::table('search_queries', function(Blueprint $table) {
            $table->dropIndex('search_queries_name_is_negative_index');
        });

        Schema::table('search_query_histories', function(Blueprint $table) {
            $table->dropIndex('search_query_histories_query_on_date');
        });
    }
}
