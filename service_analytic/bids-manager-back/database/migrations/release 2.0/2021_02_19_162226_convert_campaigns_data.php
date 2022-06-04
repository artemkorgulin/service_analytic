<?php

use App\Models\Campaign;
use App\Models\CampaignStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertCampaignsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ozon ID
        Campaign::query()
                ->join('campaign_statuses', 'campaign_statuses.id', '=', 'campaigns.campaign_status_id')
                ->whereNotIn('campaign_statuses.code', [CampaignStatus::DRAFT])
                ->update(['ozon_id' => DB::raw('campaigns.id')]);

        // Type ID
        Campaign::query()
                ->rightJoin('campaign_types', 'campaign_types.code', '=', 'campaigns.type')
                ->whereNotNull('type')
                ->update(['campaigns.type_id' => DB::raw('campaign_types.id')]);

        // Page Type ID
        Campaign::query()
                ->rightJoin('campaign_page_types', 'campaign_page_types.name', '=', 'campaigns.station_type')
                ->whereNotNull('station_type')
                ->update(['campaigns.page_type_id' => DB::raw('campaign_page_types.id')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Type ID
        Campaign::query()
                ->rightJoin('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
                ->whereNotNull('type_id')
                ->update(['campaigns.type' => DB::raw('campaign_types.code')]);

        // Page Type ID
        Campaign::query()
                ->rightJoin('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
                ->whereNotNull('page_type_id')
                ->update(['campaigns.station_type' => DB::raw('campaign_page_types.name')]);
    }
}
