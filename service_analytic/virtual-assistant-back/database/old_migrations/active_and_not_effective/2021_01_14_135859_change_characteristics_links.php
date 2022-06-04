<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCharacteristicsLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('characteristics', function (Blueprint $table) {
            $table->dropColumn('rating');
        });

        Schema::table('characteristic_search_query', function (Blueprint $table) {
            $table->rename('characteristic_root_query_search_query');
        });

        Schema::table('characteristic_root_query_search_query', function (Blueprint $table) {
            $table->dropForeign('characteristic_search_query_search_query_id_foreign');
            $table->dropColumn('search_query_id');
            $table->bigInteger('root_query_search_query_id')->unsigned()->nullable();
            $table->foreign('root_query_search_query_id', 'crqsq_root_query_search_query_id_foreign')
                  ->references('id')
                  ->on('root_query_search_query')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->integer('rating')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('characteristics', function (Blueprint $table) {
            $table->integer('rating')->unsigned()->nullable();
        });

        Schema::table('characteristic_root_query_search_query', function (Blueprint $table) {
            $table->rename('characteristic_search_query');
        });

        Schema::table('characteristic_search_query', function (Blueprint $table) {
            $table->dropForeign('crqsq_root_query_search_query_id_foreign');
            $table->dropColumn('root_query_search_query_id');
            $table->bigInteger('search_query_id')->unsigned()->nullable();
            $table->foreign('search_query_id')
                  ->references('id')
                  ->on('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->rename('characteristic_search_query');
        });
    }
}
