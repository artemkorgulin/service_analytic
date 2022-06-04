<?php

namespace Database\Seeders;

use App\Models\OzProductStatus;
use Illuminate\Database\Seeder;

class AddOzonStatusCreated extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        print("Создаю новый статус для товара Ozon created\n\n");
        if (!OzProductStatus::firstWhere('code','created'))
            OzProductStatus::create(
                [
                    'code' => 'created',
                    'name' => 'Создан (локально)'
                ]
            );
    }
}
