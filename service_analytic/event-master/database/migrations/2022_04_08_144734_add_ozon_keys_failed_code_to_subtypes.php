<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOzonKeysFailedCodeToSubtypes extends Migration
{
    const TYPE_ID = 36;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([[
            'id' => self::TYPE_ID,
            'type_id' => 7,
            'code' => 'marketplace.ozon_api_keys_failed',
            'name' => 'Срок действия токена API OZON истек'
        ]]);
        DB::table('notification_templates')->insert([[
            'template' => 'API токен от аккаунта OZON {{account}} просрочен. Свяжитесь с поддержкой по адресу team@sellerexpert.ru.',
            'lang' => 'ru',
            'subtype_id' => self::TYPE_ID
        ]]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_templates')->where('subtype_id', self::TYPE_ID)->delete();
        DB::table('notification_subtypes')->where('id', self::TYPE_ID)->delete();
    }
}
