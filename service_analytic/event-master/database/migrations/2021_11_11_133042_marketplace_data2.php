<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketplaceData2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 31, 'type_id' => 8, 'code' => 'marketplace.marketplace_product_start_upload_success', 'name' => 'Товар успешно отправлен и скоро пройдет модерацию'],
            ['id' => 32, 'type_id' => 8, 'code' => 'marketplace.marketplace_product_start_upload_fail', 'name' => 'Ошибка отправки товара в маркетплейс, попробуйте позже'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Товар успешно отправлен в {{marketplace}} и скоро пройдет модерацию.', 'lang' => 'ru', 'subtype_id' => 31],
            ['template' => 'Ошибка отправки товара в {{marketplace}}, попробуйте позже.', 'lang' => 'ru', 'subtype_id' => 32],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notifications')->where('subtype_id', 31)->delete();
        DB::table('notifications')->where('subtype_id', 32)->delete();

        DB::table('notification_templates')->where('subtype_id', 31)->delete();
        DB::table('notification_templates')->where('subtype_id', 32)->delete();

        DB::table('notification_subtypes')->where('id', 31)->delete();
        DB::table('notification_subtypes')->where('id', 32)->delete();
    }
}
