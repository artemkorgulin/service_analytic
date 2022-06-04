<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StatusSeeder::class);
        $this->call(StrategyTypeSeeder::class);
        $this->call(StrategyStatusSeeder::class);
        $this->call(CampaignTypeSeeder::class);
//        $this->call(CategorySeeder::class);
        $this->call(CampaignPageTypeSeeder::class);
        $this->call(CampaignPaymentTypeSeeder::class);
        $this->call(CampaignPlacementSeeder::class);
        $this->call(CampaignPlacementUpdateSeeder::class);
    }
}
