<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketplaceData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_types')->insert([
            ['id' => 8, 'name' => 'Личный кабинет'],
        ]);

        DB::table('notification_subtypes')->insert([
            ['id' => 28, 'type_id' => 8, 'code' => 'marketplace.account_product_upload_start', 'name' => 'Загрузка товаров началась'],
            ['id' => 29, 'type_id' => 8, 'code' => 'marketplace.account_product_upload_success', 'name' => 'Ваши товары успешно загружены'],
            ['id' => 30, 'type_id' => 8, 'code' => 'marketplace.account_product_upload_fail', 'name' => 'Произошла неудачная загрузка товаров'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Загрузка товаров началась', 'lang' => 'ru', 'subtype_id' => 28],
            ['template' => 'Для вашей учётной записи маркетплейса {{marketplace}} успешно загружено {{counted}} {{product}}.', 'lang' => 'ru', 'subtype_id' => 29],
            ['template' => 'Произошла неудачная загрузка товаров для маркетплейса {{marketplace}} и вашей учетной записи.', 'lang' => 'ru', 'subtype_id' => 30],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notifications')->where('subtype_id', 28)->delete();
        DB::table('notifications')->where('subtype_id', 29)->delete();
        DB::table('notifications')->where('subtype_id', 30)->delete();

        DB::table('notification_templates')->where('subtype_id', 28)->delete();
        DB::table('notification_templates')->where('subtype_id', 29)->delete();
        DB::table('notification_templates')->where('subtype_id', 30)->delete();

        DB::table('notification_subtypes')->where('id', 28)->delete();
        DB::table('notification_subtypes')->where('id', 29)->delete();
        DB::table('notification_subtypes')->where('id', 30)->delete();

        DB::table('notification_types')->where('id', 8)->delete();
    }
}
