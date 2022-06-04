<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountsCreateRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name')->comment('Описание роли');
        });

        $model = DB::table('table_models')->where(['model' => 'App\\Models\\Account'])->first();
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'multiply.accounts',
            'description' => 'Возможность добавлять несколько аккаунтов с одинаковым client_id или привязывать множество пользователей к одному аккаунту',
            'guard_name' => 'admin',
            'table_model_id' => $model->id,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'multiply.accounts.add',
            'guard_name' => 'admin',
            'description' => 'Возможность добавлять множество аккаунтов с одинаковым client_id',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        DB::table('role_has_permissions')->insertOrIgnore([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);

        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'multiply.users.bind.account',
            'guard_name' => 'admin',
            'description' => 'Возможность привязывать нескольких пользователей к одному аккаунту',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        DB::table('role_has_permissions')->insertOrIgnore([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
    }

    /**
     *
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $role = DB::table('roles')->where(['name' => 'multiply.accounts'])->first();

        DB::table('role_has_permissions')->where(['role_id' => $role->id])->delete();
        DB::table('roles')->where(['name' => 'multiply.accounts'])->delete();
        DB::table('permissions')->whereIn('name', ['multiply.accounts.add', 'multiply.users.bind.account'])->delete();

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['description']);
        });
    }
}
