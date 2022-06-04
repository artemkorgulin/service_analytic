<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationSubscriptionEndDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_templates')->insert([
            ['template' => 'До окончания подписки осталось {{count_days}} {{day_word}}. Выберите подходящий тариф и оплатите его в разделе <a href="/tariffs">Тарифы</a>.', 'lang' => 'ru', 'subtype_id' => 8],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_templates')->where('subtype_id', 8)->delete();
    }
}
