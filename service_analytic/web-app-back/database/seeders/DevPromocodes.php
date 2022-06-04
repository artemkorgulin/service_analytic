<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Promocode;
use \App\Models\PromocodeUser;

class DevPromocodes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Скидку на оплату услуг сервиса;
        //Бонус при оплате услуг сервиса;
        //Расширение функциональности тарифа по параметрам — максимальное кол-ва SKU, доступ к модулю;
        //Пополнение внутреннего баланса сервиса.

        Promocode::where('code', 'like', 'demo%')->delete();

        $this->createDiscountCodes();
        //$this->createBalanceCodes();
        //$this->createIncreaseSku();
    }

    public function createIncreaseSku()
    {
        Promocode::factory()
            ->active()->increaseSku(100)->multitime()
            ->create([
                'code' => 'demosku100',
            ]);
    }

    public function createBalanceCodes()
    {
        // Пополнение на 100
        Promocode::factory()
            ->active()->balance(100)->multitime()
            ->create([
                'code' => 'demobalance100',
            ]);

        // Пополнение на 1000
        for($i = 0; $i <=100; $i++){
            Promocode::factory()
                ->active()->balance(1000)->onetime()
                ->create([
                    'code' => 'demobalance1000n'.$i,
                ]);
        }
    }

    public function createDiscountCodes()
    {
        // Промокод на скидку 15%
        Promocode::factory()
            ->active()->discount(5)->unlimited()
            ->create([
                'code' => 'demodiscountunlim5',
            ]);

        // Промокод на скидку 15%
        Promocode::factory()
            ->active()->discount(15)->multitime()
            ->create([
                'code' => 'demodiscount15',
            ]);

        // Промокод на скидку 50%
        Promocode::factory()
            ->active()->discount(50)->multitime()
            ->create([
                'code' => 'demodiscount50',
            ]);

        // Промокод на 100% скидку
        Promocode::factory()
            ->active()->discount(100)->multitime()
            ->create([
                'code' => 'demodiscount100',
            ]);

        // Промокод который недоступен по времени
        Promocode::factory()
            ->stale()->discount(15)->multitime()
            ->create([
                'code' => 'demodiscount15stale',
            ]);

        // Промокод который пока неактивен
        Promocode::factory()
            ->infuture()->discount(15)->multitime()
            ->create([
                'code' => 'demodiscount15infuture',
            ]);

        // Промокод активный по времени, но выключенный
        Promocode::factory()
            ->active()->discount(15)->multitime()->disabled()
            ->create([
                'code' => 'demodiscount15disabled',
            ]);

        // Одноразовые промокоды на скидку
        for($i = 0; $i <=100; $i++){
            Promocode::factory()
                ->active()->discount(15)->onetime()
                ->create([
                    'code' => 'demosignleusediscount'.$i,
                ]);
        }
    }
}
