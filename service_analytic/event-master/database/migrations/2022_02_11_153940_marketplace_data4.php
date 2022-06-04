<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MarketplaceData4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 34, 'type_id' => 7, 'code' => 'marketplace.account_product_update_success', 'name' => 'Ваши товары успешно обновлены'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Успешно обновлено {{count}} из {{total}} товаров в {{marketplace}}.', 'lang' => 'ru', 'subtype_id' => 34],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_templates')->where('subtype_id', 34)->delete();

        DB::table('notification_subtypes')->where('id', 34)->delete();
    }
}
