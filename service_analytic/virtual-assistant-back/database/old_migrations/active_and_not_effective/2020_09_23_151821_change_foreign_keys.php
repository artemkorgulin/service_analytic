<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('root_queries', function (Blueprint $table) {
            $table->dropForeign('root_queries_alias_id_foreign');
            $table->dropColumn('alias_id');
            $table->dropColumn('brand');
            $table->dropForeign('root_queries_search_query_id_foreign');
            $table->dropColumn('search_query_id');
        });

        Schema::table('aliases', function (Blueprint $table) {
            $table->foreignId('root_query_id')
                  ->constrained('root_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropForeign('search_queries_rating_id_foreign');
            $table->dropColumn('rating_id');
            $table->smallInteger('last_rate', false, true);
            $table->foreignId('root_query_id')
                  ->constrained('root_queries')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->foreignId('search_query_id')->constrained('search_queries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('root_queries', function (Blueprint $table) {
            $table->foreignId('alias_id')->constrained('aliases');
            $table->string('brand', 50);
            $table->foreignId('search_query_id')->constrained('search_queries');
        });

        Schema::table('aliases', function (Blueprint $table) {
            $table->dropForeign('aliases_root_query_id_foreign');
            $table->dropColumn('root_query_id');
        });

        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropColumn('last_rate');
            $table->foreignId('rating_id')->constrained('ratings');
            $table->dropForeign('search_queries_root_query_id_foreign');
            $table->dropColumn('root_query_id');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign('ratings_search_query_id_foreign');
            $table->dropColumn('search_query_id');
        });
    }
}
