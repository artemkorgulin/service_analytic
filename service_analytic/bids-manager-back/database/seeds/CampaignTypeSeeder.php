<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class CampaignTypeSeeder
 */
class CampaignTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campaign_types')->insert([
            'id' => '1',
            'name' => 'SKU',
            'description' => 'Реклама товаров в спонсорских полках. Размещается на карточке товаров, в поиске и категории'
        ]);
        DB::table('campaign_types')->insert([
            'id' => '2',
            'name' => 'BANNER',
            'description' => 'Баннерная рекламная акция'
        ]);
        DB::table('campaign_types')->insert([
            'id' => '3',
            'name' => 'SIS',
            'description' => 'Реклама магазина'
        ]);
        DB::table('campaign_types')->insert([
            'id' => '4',
            'name' => 'BRAND_SHELF',
            'description' => 'Брендовая полка'
        ]);
        DB::table('campaign_types')->insert([
            'id' => '5',
            'name' => 'BOOSTING_SKU',
            'description' => 'Повышение товаров в каталоге'
        ]);
        DB::table('campaign_types')->insert([
            'id' => '6',
            'name' => 'ACTION',
            'description' => 'Рекламная кампания для селлерских акций'
        ]);
        DB::table('campaign_types')->insert([
            'id' => '7',
            'name' => 'ACTION_CAS',
            'description' => 'Рекламная кампания для акции'
        ]);
    }
}
