<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StrategyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('strategy_statuses')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('strategy_statuses')->insert(
            [
                'id' => 1,
                'name' => 'Активна',
            ]
        );

        DB::table('strategy_statuses')->insert(
            [
                'id' => 2,
                'name' => 'Неактивна',
            ]
        );
    }
}
