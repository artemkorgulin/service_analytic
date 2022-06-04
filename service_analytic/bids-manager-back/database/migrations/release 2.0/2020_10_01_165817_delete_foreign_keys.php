<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign', function (Blueprint $table) {
//            $table->dropForeign(['good_id']);
//            $table->dropForeign(['statistics_id']);
//            $table->dropColumn('good_id');
            $table->dropColumn('statistics_id');
        });

        Schema::table('good', function (Blueprint $table) {
//            $table->dropForeign(['statistics_id']);
            $table->dropColumn('statistics_id');
        });

        Schema::table('keyword', function (Blueprint $table) {
//            $table->dropForeign(['statistics_id']);
            $table->dropColumn('statistics_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
