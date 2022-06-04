<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DisableAutoIncrementPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
        });

        Schema::table('platforms', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unsignedBigInteger('id')->primary()->change();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('platform_id')->references('id')->on('platforms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
        });

        Schema::table('platforms', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(6)->change();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('platform_id')->references('id')->on('platforms');
        });
    }
}
