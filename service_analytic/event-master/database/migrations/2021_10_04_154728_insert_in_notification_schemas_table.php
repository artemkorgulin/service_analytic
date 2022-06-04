<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertInNotificationSchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = DB::connection('wab')->table('users')->get();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->id,
                'type_id' => 7,
                'way_code' => 'email',
            ];
            $data[] = [
                'user_id' => $user->id,
                'type_id' => 6,
                'way_code' => 'email',
            ];
        }
        DB::table('notification_schemas')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_schemas')->truncate();
    }
}
