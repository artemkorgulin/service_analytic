<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToSearchQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->foreignId('ozon_category_id')->nullable()
                                                        ->after('name')
                                                        ->constrained('ozon_categories')
                                                        ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('search_query_histories', function (Blueprint $table) {
            $table->foreignId('ozon_category_id')->nullable()
                                                        ->after('search_query_id')
                                                        ->constrained('ozon_categories')
                                                        ->onUpdate('cascade')->onDelete('restrict');
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
            $table->dropForeign('search_queries_ozon_category_id_foreign');
            $table->dropColumn('ozon_category_id');
        });

        Schema::table('search_query_histories', function (Blueprint $table) {
            $table->dropForeign('search_query_histories_ozon_category_id_foreign');
            $table->dropColumn('ozon_category_id');
        });
    }
}
