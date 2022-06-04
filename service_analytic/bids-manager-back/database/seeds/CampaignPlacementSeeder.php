<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


/**
 * Class CampaignPlacementSeeder
 */
class CampaignPlacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campaign_placements')->insert([
            'id' => '1',
            'code' => 'PLACEMENT_PDP',
            'description' => 'Карточка товара'
        ]);

        DB::table('campaign_placements')->insert([
            'id' => '2',
            'code' => 'PLACEMENT_SEARCH_AND_CATEGORY',
            'description' => 'Поиск'
        ]);
    }
}
