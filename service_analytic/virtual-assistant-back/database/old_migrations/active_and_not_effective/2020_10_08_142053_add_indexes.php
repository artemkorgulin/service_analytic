<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ozon_categories', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('root_queries', function (Blueprint $table) {
            $table->index(['ozon_category_id', 'name'], 'root_query_in_category_index');
        });
        Schema::table('aliases', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('negative_keywords', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('search_queries', function (Blueprint $table) {
            $table->index('name');
            $table->index('is_negative');
        });
        Schema::table('characteristics', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('search_query_histories', function (Blueprint $table) {
            $table->index(['parsing_date', 'search_query_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ozon_categories', function (Blueprint $table) {
            $table->dropIndex('ozon_categories_name_index');
        });
        Schema::table('root_queries', function (Blueprint $table) {
            $table->dropIndex('root_query_in_category_index');
        });
        Schema::table('aliases', function (Blueprint $table) {
            $table->dropIndex('aliases_name_index');
        });
        Schema::table('negative_keywords', function (Blueprint $table) {
            $table->dropIndex('negative_keywords_name_index');
        });
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropIndex('search_queries_name_index');
            $table->dropIndex('search_queries_is_negative_index');
        });
        Schema::table('characteristics', function (Blueprint $table) {
            $table->dropIndex('characteristics_name_index');
        });
        Schema::table('search_query_histories', function (Blueprint $table) {
            $table->dropIndex('search_query_histories_parsing_date_search_query_id_index');
        });
    }
}
