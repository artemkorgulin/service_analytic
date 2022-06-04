<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('statuses')->insert([
            'id' => '1',
            'code' => 'ACTIVE',
            'name' => 'Активно',
        ]);

        DB::table('statuses')->insert([
            'id' => '2',
            'code' => 'NOT_ACTIVE',
            'name' => 'Не активно',
        ]);

        DB::table('statuses')->insert([
            'id' => '3',
            'code' => 'ARCHIVED',
            'name' => 'В архиве',
        ]);

        DB::table('statuses')->insert([
            'id' => '4',
            'code' => 'DELETED',
            'name' => 'Удален в Ozon',
        ]);
    }
}
