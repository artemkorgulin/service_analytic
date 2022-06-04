<?php

namespace Database\Seeders;

use App\Models\OldTariff;
use App\Models\TariffPrice;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        TariffPrice::updateOrCreate(['id' => 1], ['currency' => 'RUB', 'price' => 0, 'vat_code' => 1]);
//        TariffPrice::updateOrCreate(['id' => 2], ['currency' => 'RUB', 'price' => 999, 'vat_code' => 1]);
//
//        OldTariff::updateOrCreate(['id' => 1], [
//            'description' => 'Бесплатный',
//            'price_id' => 1,
//            'period' => 1000,
//            'payment_subject' => 'service',
//            'payment_mode' => 'full_payment'
//        ]);
//        OldTariff::updateOrCreate(['id' => 2], [
//            'description' => 'Платный.Бета-тест. 30 дней',
//            'price_id' => 2,
//            'period' => 30,
//            'payment_subject' => 'service',
//            'payment_mode' => 'full_payment'
//        ]);
    }
}
