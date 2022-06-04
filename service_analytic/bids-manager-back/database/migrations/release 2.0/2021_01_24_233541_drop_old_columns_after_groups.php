<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropOldColumnsAfterGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_goods', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('sku');
            $table->dropColumn('price');
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('sku');
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('strategies', function (Blueprint $table) {
            $table->dropColumn('budget');
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
