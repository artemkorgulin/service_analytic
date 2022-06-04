<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCampaignsKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
//            $table->dropIndex('idx_campaign_id');
//            $table->dropIndex('campaign_id_index');
            $table->index('ozon_id');
            $table->index(['ozon_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->index('id', 'idx_campaign_id');
            $table->index('id', 'campaign_id_index');
            $table->dropIndex('ozon_id');
        });
    }
}
