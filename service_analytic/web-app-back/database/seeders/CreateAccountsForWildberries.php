<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreateAccountsForWildberries extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $wbPlatform = Platform::firstWhere('title', 'Wildberries');

        $a1 = Account::create([
            'platform_id' => $wbPlatform->id,
            'platform_client_id' => '514abb56-358f-514d-a1bb-db41c7747a24',
            'platform_api_key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6ImUxMzY1NGRmLTM4NDMtNDlmMi05MTM0LWM3ZTY4YThkN2FhMCJ9.-7iNS9eF16iLz90x4hf0_EkqT-iFFf7cSyQojDmmt2w',
            'title' => 'Wildberries Эко-Маркет',
            'description' => 'Wildberries Эко-Маркет',
            'is_active' => true
        ]);
        $a1->save();

        $a2 = Account::create([
            'platform_id' => $wbPlatform->id,
            'platform_client_id' => '291af855-87cb-58e9-8fec-240fea904cc2',
            'platform_api_key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6ImFlZjkxN2ViLWZmNTItNDZmNC1hZTI5LTliZjY4ZmI0ZTUyNyJ9.3SMGOTY7scLW-XHBtr5TNsUzyreekX7nTGvpDHbqWfo',
            'title' => 'Wildberries Эверест',
            'description' => 'Wildberries Эверест',
            'is_active' => true
        ]);
        $a2->save();


        $user1 = User::firstWhere('email', 'a.korgulin@yandex.ru');
        $user2 = User::firstWhere('email', 'a.salenkov@yandex.ru');
        $user3 = User::firstWhere('email', 'v.chursin@yandex.ru');

        $user1->accounts()->sync([
            $a1->id, $a2->id
        ]);

        $user1->accounts()->sync([
            $a1->id, $a2->id
        ]);

        $user2->accounts()->sync([
            $a1->id, $a2->id
        ]);

        $user3->accounts()->sync([
            $a1->id, $a2->id
        ]);

    }
}
