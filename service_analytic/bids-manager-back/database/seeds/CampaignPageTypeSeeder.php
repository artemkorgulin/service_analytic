<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


/**
 * Class CampaignPageTypeSeeder
 */
class CampaignPageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campaign_page_types')->insert([
            'id' => '1',
            'name' => 'Поиск'
        ]);

        DB::table('campaign_page_types')->insert([
            'id' => '2',
            'name' => 'Карточка товара и категории'
        ]);

        DB::table('campaign_page_types')->insert([
            'id' => '3',
            'name' => 'Тестовая компания'
        ]);
    }
}
