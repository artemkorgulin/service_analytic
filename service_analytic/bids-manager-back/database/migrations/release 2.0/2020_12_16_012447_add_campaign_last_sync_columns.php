<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignLastSyncColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->dateTime('last_ozon_sync')->nullable();
            $table->dateTime('last_vp_sync')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->dropColumn('last_ozon_sync');
            $table->dropColumn('last_vp_sync');
        });
    }
}
