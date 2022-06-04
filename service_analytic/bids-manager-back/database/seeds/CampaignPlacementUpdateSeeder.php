<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


/**
 * Class CampaignPlacementUpdateSeeder
 */
class CampaignPlacementUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campaign_placements')
            ->where('code', 'PLACEMENT_SEARCH_AND_CATEGORY')
            ->update([
                'description' => 'Поиск'
            ]);
    }
}
