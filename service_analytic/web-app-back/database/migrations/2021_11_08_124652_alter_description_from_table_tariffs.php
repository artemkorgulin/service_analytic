<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDescriptionFromTableTariffs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_tariffs', function (Blueprint $table) {
            // Бесплатныей тариф
            DB::table('tariffs')->where('tariff_id' ,'=',  2)->update( ['name' => 'Оптимизация карточек товара', 'description' => 'Динамика позиций в категории']);
            DB::table('tariffs')->where('tariff_id' ,'=',  3)->update( ['name' => 'Оптимизация карточек товара', 'description' => 'Оптимизация видимости в категории|Оптимизация по поисковым запросам|Динамика позиций в категории|Динамика позиций по запросам (WB)']);
            DB::table('tariffs')->where('tariff_id' ,'=',  4)->update( ['name' => 'Оптимизация карточек товара', 'description' => 'Оптимизация видимости в категории|Оптимизация по поисковым запросам|Динамика позиций в категории|Динамика позиций по запросам (WB)']);
            DB::table('tariffs')->where('tariff_id' ,'=',  5)->update( ['name' => 'Управление рекламными кампаниями', 'description' => 'Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);
            DB::table('tariffs')->where('tariff_id' ,'=',  6)->update( ['name' => 'Управление рекламными кампаниями','description' => 'Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);
            DB::table('tariffs')->where('tariff_id' ,'=',  7)->update( ['name' => 'Аналитика маркетплейсов',  'description' => 'Статистика продаж конкурентов']);
            DB::table('tariffs')->where('tariff_id' ,'=',  8)->update( ['name' => 'Аналитика маркетплейсов',  'description' => 'Статистика продаж конкурентов']);

            // Бесплатные тарифы
            DB::table('tariffs')->where('tariff_id' ,'=',  14)->update( ['visible' => 1, 'active' => 0, 'name' => 'Управление рекламными кампаниями', 'description' => 'Подбор семантического ядра|Продвижение по стратегии|Аналитика РК']);
            DB::table('tariffs')->where('tariff_id' ,'=',  15)->update( ['visible' => 1, 'active' => 0, 'name' => 'Аналитика маркетплейсов',  'description' => 'Статистика продаж конкурентов']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_tariffs', function (Blueprint $table) {
            //
        });
    }
}
