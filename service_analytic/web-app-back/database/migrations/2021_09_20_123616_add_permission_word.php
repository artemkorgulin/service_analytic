<?php

use Illuminate\Database\Migrations\Migration;

class AddPermissionWord extends Migration
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
                'name' => 'brand.ozon',
                'guard_name' => 'admin',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'brand.wb',
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
        DB::table('permissions')->whereIn('name', ['brand.ozon', 'brand.wb'])->delete();
    }
}
