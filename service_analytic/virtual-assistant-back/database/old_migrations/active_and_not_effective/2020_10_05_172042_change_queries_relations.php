<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeQueriesRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropForeign('search_queries_root_query_id_foreign');
            $table->dropColumn('root_query_id');
            $table->boolean('is_negative')->comment('Содержит стоп-слово');
        });

        Schema::create('root_query_search_query', function (Blueprint $table) {
            $table->id();
            $table->foreignId('root_query_id')
                  ->constrained('root_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('search_query_id')
                  ->constrained('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::table('characteristics', function (Blueprint $table) {
            $table->dropForeign('characteristics_search_query_id_foreign');
            $table->dropColumn('search_query_id');
        });

        Schema::create('characteristic_search_query', function (Blueprint $table) {
            $table->id();
            $table->foreignId('characteristic_id')
                  ->constrained('characteristics')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('search_query_id')
                  ->constrained('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
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
        Schema::dropIfExists('characteristic_search_query');

        Schema::table('search_queries', function (Blueprint $table) {
            $table->foreignId('root_query_id')
                  ->constrained('root_queries')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->dropColumn('is_negative');
        });
        Schema::table('characteristics', function (Blueprint $table) {
            $table->foreignId('search_query_id')
                  ->constrained('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }
}
