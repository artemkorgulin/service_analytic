<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TechnicalTarifEnd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 33, 'type_id' => 1, 'code' => 'technical.tarif_ended', 'name' => 'Период действия вашего тарифа завершен: доступно редактирование не более 3 товаров. Выберите подходящий тариф и оплатите его в разделе Тарифы'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Период действия вашего тарифа завершен: доступно редактирование не более 3 товаров. Выберите подходящий тариф и оплатите его в разделе Тарифы', 'lang' => 'ru', 'subtype_id' => 33],
        ]);

        $users = DB::connection('wab')->table('users')->get();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->id,
                'type_id' => 1,
                'way_code' => 'email',
            ];
        }
        DB::table('notification_schemas')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_templates')->where('subtype_id', 33)->delete();

        DB::table('notification_subtypes')->where('id', 33)->delete();

        DB::table('notification_schemas')->where('type_id', 1)->delete();
    }
}
