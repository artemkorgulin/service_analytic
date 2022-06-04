<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevenueToStatisticsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->float('revenue', 8, 2)->nullable();
        });

        Schema::table('good_statistics', function (Blueprint $table) {
            $table->float('revenue', 8, 2)->nullable();
        });

        Schema::table('keyword_statistics', function (Blueprint $table) {
            $table->float('revenue', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->dropColumn('revenue');
        });

        Schema::table('good_statistics', function (Blueprint $table) {
            $table->dropColumn('revenue');
        });

        Schema::table('keyword_statistics', function (Blueprint $table) {
            $table->dropColumn('revenue');
        });
    }
}
