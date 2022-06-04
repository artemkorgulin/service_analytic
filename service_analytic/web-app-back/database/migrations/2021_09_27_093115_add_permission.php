<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currentDateTime = date('Y-m-d H:i:s');
        DB::table('permissions')->insert([
            [
                'name' => 'good.access',
                'guard_name' => 'admin',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'promotion.access',
                'guard_name' => 'admin',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->whereIn('name', ['good.access', 'promotion.access'])->delete();
    }
}
