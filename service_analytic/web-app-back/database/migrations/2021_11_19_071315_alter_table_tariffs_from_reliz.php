<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffsFromReliz extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Тестовая РК в описании
        DB::table('tariffs')->where('tariff_id' ,'=',  5)->update( ['name' => 'Управление рекламными кампаниями', 'description' => 'Тестовая РК|Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);
        DB::table('tariffs')->where('tariff_id' ,'=',  6)->update( ['name' => 'Управление рекламными кампаниями','description' => 'Тестовая РК|Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);

        DB::table('tariffs')->where('tariff_id' ,'=',  7)->update( ['description' => 'Статистика продаж конкурентов|Ассортиментное сравнение по бренду|Ценовое сравнение по предметам']);
        DB::table('tariffs')->where('tariff_id' ,'=',  8)->update( ['description' => 'Статистика продаж конкурентов|Ассортиментное сравнение по бренду|Ценовое сравнение по предметам']);

        DB::table('tariffs')->insert([
            // Корпоративный доступ
            ['tariff_id' => 16, 'price_id'=> 16, 'name' => 'Мониторинг цен', 'sku' => 30, 'description' => 'Мониторинг РРЦ|Мониторинг цен конкурентов', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 17, 'price_id'=> 17, 'name' => 'Мониторинг цен', 'sku' => 100, 'description' => 'Мониторинг РРЦ|Мониторинг цен конкурентов', 'visible' => 1, 'active' => 0],
        ]);

        DB::table('tariffs')->insert([
            // Корпоративный доступ
            ['tariff_id' => 18, 'price_id'=> 18, 'price_tariff_id' => 5000, 'name' => 'Корпоративный доступ', 'sku' => 30, 'description' => 'Доступ к работе сервиса до 10 человек одновременно', 'visible' => 1, 'active' => 0],
            ['tariff_id' => 19, 'price_id'=> 19, 'price_tariff_id' => 5000, 'name' => 'Корпоративный доступ', 'sku' => 100, 'description' => 'Доступ к работе сервиса до 10 человек одновременно', 'visible' => 1, 'active' => 0],
        ]);

        DB::table('tariff_prices')->insert([
            ['tariff_id' => 14, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 15, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 16, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 17, 'currency' => 'RUB', 'price' =>  '0', 'vat_code' => '1'],
            ['tariff_id' => 18, 'currency' => 'RUB', 'price' =>  '5000', 'vat_code' => '1'],
            ['tariff_id' => 19, 'currency' => 'RUB', 'price' =>  '5000', 'vat_code' => '1'],
        ]);

        Schema::table('tariffs', function(Blueprint $table)
        {
            $table->renameColumn('price_tariff_id', 'tariff_price');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Тестовая РК в описании
        DB::table('tariffs')->where('tariff_id' ,'=',  5)->update( ['name' => 'Управление рекламными кампаниями', 'description' => 'Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);
        DB::table('tariffs')->where('tariff_id' ,'=',  6)->update( ['name' => 'Управление рекламными кампаниями','description' => 'Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);

        DB::table('tariffs')->where('tariff_id' ,'=',  7)->update( ['description' => 'Статистика продаж конкурентов|Ассортиментное сравнение по бренду|Ценовое сравнение по предметам']);
        DB::table('tariffs')->where('tariff_id' ,'=',  8)->update( ['description' => 'Статистика продаж конкурентов|Ассортиментное сравнение по бренду|Ценовое сравнение по предметам']);


        DB::table('tariffs')->whereIn('tariff_id', [16,17,18,19])->delete();
        DB::table('tariff_prices')->whereIn('tariff_id', [14,15,16,17,18,19])->delete();

        Schema::table('tariffs', function(Blueprint $table)
        {
            $table->renameColumn('tariff_price', 'price_tariff_id');

        });


    }
}
