<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSearchQueryParametersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropForeign('search_queries_ozon_category_id_foreign');
            $table->dropColumn('ozon_category_id');
            $table->dropColumn('popularity');
            $table->dropColumn('additions_to_cart');
            $table->dropColumn('avg_price');
            $table->dropColumn('products_count');
            $table->dropColumn('rating');
            $table->dropColumn('filtering_ratio');
        });

        Schema::table('root_query_search_query', function (Blueprint $table) {
            $table->timestamp('created_at')->after('id')->nullable();
            $table->timestamp('updated_at')->after('created_at')->nullable();
            $table->integer('popularity')->unsigned()->nullable();
            $table->integer('additions_to_cart')->unsigned()->nullable();
            $table->integer('avg_price')->unsigned()->nullable();
            $table->integer('products_count')->unsigned()->nullable();
            $table->integer('rating')->unsigned()->nullable();
            $table->decimal('filtering_ratio')->unsigned()->nullable();
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
            $table->foreignId('ozon_category_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('ozon_categories')
                  ->onUpdate('cascade')->onDelete('restrict');
            $table->integer('popularity')->after('ozon_category_id')->unsigned()->nullable();
            $table->integer('additions_to_cart')->after('popularity')->unsigned()->nullable();
            $table->integer('avg_price')->after('additions_to_cart')->unsigned()->nullable();
            $table->integer('products_count')->after('avg_price')->unsigned()->nullable();
            $table->integer('rating')->after('products_count')->unsigned()->nullable();
            $table->decimal('filtering_ratio')->after('rating')->unsigned()->nullable();
        });

        Schema::table('root_query_search_query', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('popularity');
            $table->dropColumn('additions_to_cart');
            $table->dropColumn('avg_price');
            $table->dropColumn('products_count');
            $table->dropColumn('rating');
            $table->dropColumn('filtering_ratio');
        });
    }
}
