<?php

namespace Database\Seeders;

use Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->updateOrInsert(
            [
                'email' => 'itech@itech-group.ru',
            ],
            [
                'name' => 'ITECH.solutions',
                'email' => 'itech@itech-group.ru',
                'password' => Hash::make('city0101'),
                'api_token' => Str::random(60),
                'email_verified_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            [
                'email' => 'cn@yandex.ru',
            ],
            [
                'name' => 'Analitics',
                'email' => 'cn@tandex.ru',
                'password' => Hash::make('city0101'),
                'api_token' => Str::random(60),
                'email_verified_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            [
                'email' => 'development@truemachine.ru',
            ],
            [
                'name' => 'TrueMachine',
                'password' => Hash::make('truemachine2021'),
                'api_token' => Str::random(60),
                'email_verified_at' => \Carbon\Carbon::now(),
            ]
        );
    }
}
