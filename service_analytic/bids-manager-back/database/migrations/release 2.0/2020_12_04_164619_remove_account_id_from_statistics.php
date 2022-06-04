<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAccountIdFromStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });

        Schema::table('good_statistics', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });

        Schema::table('keyword_statistics', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function (Blueprint $table) {
            //
        });
    }
}
