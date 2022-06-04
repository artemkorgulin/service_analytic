<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSearchQueryRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::table('search_query_ranks', function (Blueprint $table) {
//            $table->timestamp('created_at')->after('id')->nullable();
//            $table->timestamp('updated_at')->after('created_at')->nullable();
//            $table->dropColumn('rank');
//            $table->decimal('filtering_ratio')->unsigned()->nullable();
//            $table->date('date')->after('filtering_ratio')->change();
//        });
        Schema::table('search_queries', function (Blueprint $table) {
            $table->decimal('filtering_ratio')->after('rating')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('search_query_ranks', function (Blueprint $table) {
//            $table->dropColumn('created_at');
//            $table->dropColumn('updated_at');
//            $table->dropColumn('filtering_ratio');
//            $table->tinyInteger('rank');
//            $table->dateTime('date')->change();
//        });
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropColumn('filtering_ratio');
        });
    }
}
