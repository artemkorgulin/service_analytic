<?php

use App\Models\NegativeKeyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NegativeKeywordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('negative_keywords')->truncate();

        NegativeKeyword::create(['name' => 'baby fox']);
        NegativeKeyword::create(['name' => 'depend']);
        NegativeKeyword::create(['name' => 'binshuai']);
        NegativeKeyword::create(['name' => 'cotril']);
        NegativeKeyword::create(['name' => 'cat eye']);
        NegativeKeyword::create(['name' => 'contra']);
        NegativeKeyword::create(['name' => 'colorpanda']);
        NegativeKeyword::create(['name' => 'apex']);
        NegativeKeyword::create(['name' => 'bertoni']);
        NegativeKeyword::create(['name' => 'aromaticat']);
    }
}
