<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


/**
 * Class CampaignPaymentTypeSeeder
 */
class CampaignPaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campaign_payment_types')->insert([
            'id' => '1',
            'name' => 'Показы'
        ]);

        DB::table('campaign_payment_types')->insert([
            'id' => '2',
            'name' => 'Клики'
        ]);
    }
}
