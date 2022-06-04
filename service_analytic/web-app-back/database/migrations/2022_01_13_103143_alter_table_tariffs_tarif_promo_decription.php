<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffsTarifPromoDecription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)->update( ['description' => 'Удобные графики для анализа продаж и видимости товаров|Оптимизация карточек под требования маркетплейса|Поисковая SEO-оптимизация карточек|Рекомендации, разработанные специально для ваших товаров']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)->update( ['description' => 'Оптимизация видимости в категории|Оптимизация по поисковым запросам|Динамика позиций в категории|Динамика позиций по запросам (WB)']);
    }
}
