<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BillingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 27, 'type_id' => 2, 'code' => 'billing.invoice_success', 'name' => 'Успешная оплата пакета по счету'],
        ]);

        DB::table('notification_templates')->insert([
            ['template' => 'Успешная оплата пакета по счету на сумму: {{price}} р.', 'lang' => 'ru', 'subtype_id' => 27],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notifications')->where('subtype_id', 27)->delete();

        DB::table('notification_templates')->where('subtype_id', 27)->delete();

        DB::table('notification_subtypes')->where('id', 27)->delete();
    }
}
