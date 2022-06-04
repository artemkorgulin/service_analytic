<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffsPromoDecription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tariffs')->where('tariff_id' ,'=',  1)->update( ['description' => 'Оптимизация видимости в категории|Оптимизация по поисковым запросам|Динамика позиций в категории|Динамика позиций по запросам (WB)']);
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
