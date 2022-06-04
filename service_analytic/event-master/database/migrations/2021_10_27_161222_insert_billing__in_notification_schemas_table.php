<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertBillingInNotificationSchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')->insert([
            ['id' => 26, 'type_id' => 2, 'code' => 'billing.card_success', 'name' => 'Успешная оплата пакета'],
        ]);
        $users = DB::connection('wab')->table('users')->get();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->id,
                'type_id' => 2,
                'way_code' => 'email',
            ];
        }
        DB::table('notification_schemas')->insert($data);

        DB::table('notification_templates')->insert([
            ['template' => 'Успешная оплата пакета на сумму: {{price}} р.', 'lang' => 'ru', 'subtype_id' => 26],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notifications')->where('subtype_id', 26)->delete();

        DB::table('notification_templates')->where('subtype_id', 26)->delete();

        DB::table('notification_subtypes')->where('id', 26)->delete();

        DB::table('notification_schemas')->where('type_id', 2)->delete();
    }
}
