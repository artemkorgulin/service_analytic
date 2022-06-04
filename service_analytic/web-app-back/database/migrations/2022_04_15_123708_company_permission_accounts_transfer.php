<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CompanyPermissionAccountsTransfer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = Carbon::now();

        DB::table('permissions')->insert([
            'name' => 'company.accounts.transfer',
            'description' => 'Переносить аккаунты',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        $permissionAccountsTransferId = DB::table('permissions')->where('name', 'company.accounts.transfer')->first()->id;

        $companyOwnerId = DB::table('roles')->where('name', 'company.owner')->first()->id;

        DB::table('role_has_permissions')->insert(
            [
                'permission_id' => $permissionAccountsTransferId,
                'role_id' => $companyOwnerId,
            ]
        );
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
