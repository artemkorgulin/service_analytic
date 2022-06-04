<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Tariff;
use App\Models\Service;

class Billing2022Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach(['tariffs', 'services', 'service_prices', 'service_tariff'] as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $service = Service::create([
            'name' => 'Корпоративный доступ',
            'description' => 'Доступ для сотрудников',
            'visible' => true
        ]);
        $service->prices()->create(['min_amount'=>0, 'price_per_item'=>200*100]);

        $basic_services = [];
        foreach(['Депонирование', 'Рекламные компании', 'Семантический словарь', 'Аналитика'] as $name){
            $service = Service::create([
                'name' => $name,
                'description' => '',
                'visible' => false
            ]);
            $service->prices()->create(['min_amount'=>0, 'price_per_item'=>29*100]);
            $service->prices()->create(['min_amount'=>100, 'price_per_item'=>15*100]);
            $service->prices()->create(['min_amount'=>500, 'price_per_item'=>10*100]);
            $basic_services[] = $service;
        }

        $tariff = Tariff::create([
            'name' => 'Промо тариф',
            'description' => 'Доступ ко всему',
            'price' => 2990 * 100,
            'visible' => true
        ]);

        foreach($basic_services as $service){
            $tariff->services()->attach($service->id, ['amount'=>100]);
        }

    }
}
