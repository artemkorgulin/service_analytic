<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StrategyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('strategy_types')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('strategy_types')->insert(
            [
                'id'       => 1,
                'name'     => 'Оптимальное количество показов',
                'behavior' => 'shows'
            ]
        );

        DB::table('strategy_types')->insert(
            [
                'id'       => 2,
                'name'     => 'Оптимизация по CPO',
                'behavior' => 'cpo'
            ]
        );
    }
}
