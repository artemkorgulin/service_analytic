<?php

namespace Database\Seeders;

use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;
use App\Services\UserService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class AddAccountIdToOzProduct extends Seeder
{

    private $OzonPlatformId = 1;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [];

        $bearerToken = UserService::auth();

        $url = env('WEB_APP_API_URL', '') . '/v1/get-all-users-and-accounts';

        $response = Http::withToken($bearerToken)->get($url)->json();

        foreach ($response as $user) {
            foreach ($user['accounts'] as $account) {
                if ($account['platform_id'] == $this->OzonPlatformId) {
                    $users[$account['pivot']['user_id']] = $account['pivot']['account_id'];
                    if ($account['pivot']['is_selected']) {
                        break;
                    }
                }
            }
        }


        // Заполняем в таблице OzProducts поле account_id
        $products = OzProduct::all();
        foreach ($products as $product) {
            if (isset($users[$product->user_id])) {
                $product->account_id = $users[$product->user_id];
                $product->save();
            }
        }

        // Заполняем тоже самое в таблице OzTemporaryProduct
        $products = OzTemporaryProduct::all();
        foreach ($products as $product) {
            if (isset($users[$product->user_id])) {
                $product->account_id = $users[$product->user_id];
                $product->save();
            }
        }
    }
}
