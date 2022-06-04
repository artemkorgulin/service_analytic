<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketplaceData5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 35, 'type_id' => 7, 'code' => 'marketplace.wb_api_keys_failed', 'name' => 'Срок действия токена API Wildberries истек'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'API токен от аккаунта Wildberries {{account}} просрочен. Свяжитесь с поддержкой по адресу team@sellerexpert.ru.', 'lang' => 'ru', 'subtype_id' => 35],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_templates')->where('subtype_id', 35)->delete();

        DB::table('notification_subtypes')->where('id', 35)->delete();
    }
}
