<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CompanyRolesRename extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')
            ->where('name', 'company.admin')
            ->update([
                'description' => 'Владелец',
            ]);

        DB::table('roles')
            ->where('name', 'company.manager')
            ->update([
                'description' => 'Контент',
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
