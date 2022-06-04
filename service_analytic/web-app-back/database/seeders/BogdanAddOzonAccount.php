<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class BogdanAddOzonAccount extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstWhere('email', 'bogdan.pirozhok@gmail.com');
        $account = Account::findOrFail(3);
        $user->accounts()->attach($account->id);
    }
}
