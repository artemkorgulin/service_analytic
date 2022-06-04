<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SignalProductsProblemNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 38, 'type_id' => 3, 'code' => 'card_product.product_problem', 'name' => 'Проблемы в товарах'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'На дату {{date}} выявлена проблема ({{message}}) <a href="{{url}}">ссылка на отчет</a>', 'lang' => 'ru', 'subtype_id' => 38],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notifications')->where('subtype_id', 38)->delete();

        DB::table('notification_templates')->where('subtype_id', 38)->delete();

        DB::table('notification_subtypes')->where('id', 38)->delete();
    }
}
