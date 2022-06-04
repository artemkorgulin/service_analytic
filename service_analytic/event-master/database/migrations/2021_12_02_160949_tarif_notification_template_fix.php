<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TarifNotificationTemplateFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_templates')
            ->where('subtype_id', 8)
            ->where('lang', 'ru')
            ->update([
                'template' => 'До окончания подписки осталось {{count_days}} {{day_word}}. Выберите подходящий тариф и оплатите его в разделе <a href="'. env('FRONT_APP_URL') .'/tariffs">Тарифы</a>.'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
