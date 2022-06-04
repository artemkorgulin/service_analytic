<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountProductsDeleteNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 39, 'type_id' => 7, 'code' => 'private_office.company_corporate_end', 'name' => 'Корпоративный тариф закончился'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Корпоративный тариф для компании {{company}} не активен, если он не будет оплачен до {{date}}, данные по вашим товарам в отслеживании будут удалены', 'lang' => 'ru', 'subtype_id' => 39],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notifications')->where('subtype_id', 39)->delete();

        DB::table('notification_templates')->where('subtype_id', 39)->delete();

        DB::table('notification_subtypes')->where('id', 39)->delete();
    }
}
