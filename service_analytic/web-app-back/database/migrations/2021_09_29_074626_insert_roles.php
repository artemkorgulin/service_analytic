<?php

use Illuminate\Database\Migrations\Migration;

class InsertRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currentDateTime = \Carbon\Carbon::now()->toDateTimeString();
        DB::table('roles')->insert([
            [
                'name' => 'super.supplier',
                'guard_name' => 'admin',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'wildberries.supplier',
                'guard_name' => 'admin',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'ozon.seller.supplier',
                'guard_name' => 'admin',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'ozon.performance.supplier',
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
        DB::table('roles')->whereIn(
            'name',
            ['super.supplier', 'wildberries.supplier', 'ozon.seller.supplier', 'ozon.performance.supplier']
        )->delete();
    }
}
