<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DeleteDublicateAndEmptyDataInGoodsAndCampaignsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('campaigns')
            ->leftJoin('campaign_goods AS cg', 'campaigns.id', '=', 'cg.campaign_id')
            ->leftJoin('strategies AS s', 'campaigns.id', '=', 's.campaign_id')
            ->leftJoin('campaign_statistics AS cs', 'campaigns.id', '=', 'cs.campaign_id')
            ->whereNull('cg.campaign_id')
            ->whereNull('s.campaign_id')
            ->whereNull('cs.campaign_id')
            ->delete();

        DB::table('goods')
            ->leftJoin('campaign_goods AS cg', 'goods.id', '=', 'cg.good_id')
            ->whereNull('cg.good_id')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
