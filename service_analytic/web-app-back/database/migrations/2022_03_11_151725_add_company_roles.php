<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = Carbon::now();

        DB::table('table_models')->insert(
            [
                'model' => 'App\Models\UserCompany',
                'name' => 'Модель связки пользователя с компанией',
                'created_at' => $date,
                'updated_at' => $date
            ]);
        $tableModelId = DB::table('table_models')->where('model', 'App\Models\UserCompany')->first()->id;

        DB::table('roles')->insert([
            'name' => 'company.admin',
            'description' => 'Владелец(администратора) компании',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
            'table_model_id' => $tableModelId,
        ]);
        $companyAdminId =DB::table('roles')->where('name', 'company.admin')->first()->id;
            DB::table('roles')->insert([
            'name' => 'company.manager',
            'description' => 'Менеджер компании',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
            'table_model_id' => $tableModelId,
        ]);

        DB::table('permissions')->insert([
            'name' => 'company.list',
            'description' => 'Получить список компаний',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        DB::table('permissions')->insert([
            'name' => 'company.show',
            'description' => 'Получить информацию по компании',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        DB::table('permissions')->insert([
            'name' => 'company.create',
            'description' => 'Создать компанию',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        DB::table('permissions')->insert([
            'name' => 'company.edit',
            'description' => 'Редактировать компанию',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        DB::table('permissions')->insert([
            'name' => 'company.delete',
            'description' => 'Удалить компанию',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        DB::table('permissions')->insert([
            'name' => 'company.users.list',
            'description' => 'Просматривать список сотрудников компании',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        DB::table('permissions')->insert([
            'name' => 'company.users.add',
            'description' => 'Добавить сотрудника в компанию',
            'guard_name' => 'company',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $companyPermissions = DB::table('permissions')->where('guard_name', 'company')->get();
        foreach ($companyPermissions as $companyPermission) {
            DB::table('role_has_permissions')->insert(
                [
                    'permission_id' => $companyPermission->id,
                    'role_id' => $companyAdminId,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->where('name', 'company.admin')->delete();
        DB::table('roles')->where('name', 'company.manager')->delete();

        DB::table('permissions')->where('guard_name', 'company')->delete();

        DB::table('table_models')->where('model', 'App\Models\UserCompany')->delete();
    }
}
