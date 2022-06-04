<?php

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(OzonCategorySeeder::class);
        $this->call(RootQuerySeeder::class);
        $this->call(NegativeKeywordsSeeder::class);
    }
}
