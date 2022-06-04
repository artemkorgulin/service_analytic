<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Platform::create([
            'title' => 'Ozon',
            'logo' => '/storage/platforms/ozon_logo.png',
            'description' => 'Озон крупнейшая площадка интернета',
            'api_desc_url' => 'https://api-seller.ozon.ru/docs/',
            'settings' => json_encode([
                'ClientId' => 'Введите ID клиента',
                'ApiKey' => 'Api key для работы',
            ]),
        ]);

        \App\Models\Platform::create([
            'title' => 'Wildberries',
            'logo' => '/storage/platforms/wildberries.png',
            'description' => 'Wildberries крупнейшая площадка интернета',
            'api_desc_url' => 'https://suppliers-api.wildberries.ru/swagger/index.html',
            'settings' => json_encode([
                'supplierId' => 'Введите ID клиента (поставщика)',
                'apiKey' => 'Api key для работы',
            ]),
        ]);
    }
}
