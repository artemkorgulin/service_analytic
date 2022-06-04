<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSearchQueriesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->integer('popularity')->unsigned()->nullable()->change();
            $table->integer('additions_to_cart')->unsigned()->nullable()->change();
            $table->integer('avg_price')->unsigned()->nullable()->change();
            $table->integer('products_count')->unsigned()->nullable()->change();
            $table->integer('rating')->unsigned()->nullable()->change();
            $table->string('name', 255)->change();
        });
        Schema::table('search_query_histories', function (Blueprint $table) {
            $table->integer('products_count')->unsigned()->nullable()->change();
            $table->integer('rating')->unsigned()->nullable()->change();
        });
        Schema::table('characteristics', function (Blueprint $table) {
            $table->string('name', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->integer('popularity')->unsigned()->change();
            $table->integer('additions_to_cart')->unsigned()->change();
            $table->integer('avg_price')->unsigned()->change();
            $table->integer('products_count')->unsigned()->change();
            $table->integer('rating')->unsigned()->change();
        });
        Schema::table('search_query_histories', function (Blueprint $table) {
            $table->integer('products_count')->unsigned()->change();
            $table->integer('rating')->unsigned()->change();
        });
    }
}
