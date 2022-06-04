<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRequiredCollectionWbCharacteristicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('required_collection_wb_characteristics', function (Blueprint $table) {
            $table->id();
            $table->string('characteristic');
            $table->timestamps();
        });

        $array = [
            'Назначение',
            'Назначение аромата',
            'Назначение бандажа/ортеза',
            'Назначение белья/купальника',
            'Назначение дезодоранта',
            'Назначение интимной помпы',
            'Назначение косметики для животных',
            'Назначение косметики для обуви/одежды',
            'Назначение косметического прибора',
            'Назначение косметического средства',
            'Назначение косметической кисти',
            'Назначение костюма',
            'Назначение носков',
            'Назначение ноутбука',
            'Назначение обложки',
            'Назначение обуви',
            'Назначение одноразового изделия',
            'Назначение перчаток',
            'Назначение платья',
            'Назначение подводки',
            'Назначение ремня сумки',
            'Назначение рюкзака',
            'Назначение сыворотки',
            'Назначение тату машинки',
            'Назначение фаты',
            'Наличие мембраны',
            'Основной цвет',
            'Пол',
            'Пол велосипеда',
            'Пол животного',
            'Пол куклы',
            'Пол ребенка',
            'Прямые поставки от производителя',
            'Раздел меню',
            'Размер',
            'Рос. размер',
            'Состав',
            'Тнвэд',
            'Уценка',
            'Хрупкость',
            'Страна производства',
            'Бренд'
        ];

        foreach($array as $item){
            DB::table('required_collection_wb_characteristics')->insert([
                'characteristic' => $item
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('required_collection_wb_characteristics');
    }
}
