<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyUserNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 37, 'type_id' => 7, 'code' => 'company.add_user', 'name' => 'Приглашение в компанию'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Вам предоставлен доступ к {{company}}. Чтобы начать работать с компанией, перейдите в <a href="' . env('FRONT_APP_URL') . '">личный кабинет</a> SellerExpert.', 'lang' => 'ru', 'subtype_id' => 37],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_templates')->where('subtype_id', 37)->delete();

        DB::table('notification_subtypes')->where('id', 37)->delete();
    }
}
