<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CompanyRolesRename2 extends Migration
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
            'name' => 'company.products.add',
            'description' => 'Добавлять товары в отслеживание',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        $permissionProductAddId = DB::table('permissions')->where('name', 'company.products.add')->first()->id;

        $tableModelId = DB::table('table_models')->where('model', 'App\Models\UserCompany')->first()->id;

        DB::table('roles')
            ->where('name', 'company.admin')
            ->update([
                'name' => 'company.owner',
                'description' => 'Владелец',
            ]);

        DB::table('roles')
            ->where('name', 'company.manager')
            ->update([
                'name' => 'company.admin',
                'description' => 'Администратор',
            ]);
        $companyAdminId = DB::table('roles')->where('name', 'company.admin')->first()->id;

        $permissionCompanyListId = DB::table('permissions')->where('name', 'company.list')->first()->id;
        DB::table('role_has_permissions')->insert(
            [
                'permission_id' => $permissionCompanyListId,
                'role_id' => $companyAdminId,
            ]
        );

        $permissionCompanyShowId = DB::table('permissions')->where('name', 'company.show')->first()->id;
        DB::table('role_has_permissions')->insert(
            [
                'permission_id' => $permissionCompanyShowId,
                'role_id' => $companyAdminId,
            ]
        );

        $permissionCompanyUsersListId = DB::table('permissions')->where('name', 'company.users.list')->first()->id;
        DB::table('role_has_permissions')->insert(
            [
                'permission_id' => $permissionCompanyUsersListId,
                'role_id' => $companyAdminId,
            ]
        );

        $permissionCompanyUsersAddId = DB::table('permissions')->where('name', 'company.users.add')->first()->id;
        DB::table('role_has_permissions')->insert(
            [
                'permission_id' => $permissionCompanyUsersAddId,
                'role_id' => $companyAdminId,
            ]
        );

        DB::table('roles')->insert([
            'name' => 'company.manager',
            'description' => 'Контент',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
            'table_model_id' => $tableModelId,
        ]);
        $companyManagerId = DB::table('roles')->where('name', 'company.manager')->first()->id;

        DB::table('role_has_permissions')->insert(
            [
                'permission_id' => $permissionProductAddId,
                'role_id' => $companyManagerId,
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
