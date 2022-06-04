<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class AddBrandStoreWithoutRestrictionsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = DB::table('roles')->where(['name' => 'partner'])->first();
        $model = DB::table('table_models')->where(['model' => 'App\\Models\\User'])->first();

        if (empty($role)) {
            DB::table('roles')->insert([
                'name' => 'partner',
                'description' => 'Возможность добавлять товары без ограничений по брендам',
                'guard_name' => 'admin',
                'table_model_id' => $model->id,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);
            $role = DB::table('roles')->where(['name' => 'partner'])->first();
        }

        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'brand.store_without_restrictions',
            'guard_name' => 'admin',
            'description' => 'Возможность добавлять товары без ограничений по брендам',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        DB::table('role_has_permissions')->insertOrIgnore([
            'role_id' => $role->id,
            'permission_id' => $permissionId,
        ]);
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $role = DB::table('roles')->where(['name' => 'partner'])->first();
        DB::table('role_has_permissions')->where(['role_id' => $role->id])->delete();
        DB::table('roles')->where(['name' => 'partner'])->delete();
        DB::table('permissions')->whereIn('name', ['brand.store_without_restrictions'])->delete();
    }
}
