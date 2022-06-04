<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertIntoPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = DB::table('users')->get()->all();
        $roleId = optional(DB::table('roles')->where('name', '=', 'user')->first('id'))->id;

        if ($roleId) {
            $insertData = [];
            foreach ($users as $user) {
                $insertData[] = [
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id
                ];
            }

            DB::table('model_has_roles')->insertOrIgnore($insertData);
        }

        $userId = optional(DB::table('users')->where('email', '=', 'toyij39180@tripaco.com')
            ->first('id'))->id;
        $roleId = optional(DB::table('roles')->where('name', '=', 'admin')->first('id'))->id;

        if (!empty($userId) && !empty($roleId)) {
            DB::table('model_has_roles')->insertOrIgnore([[
                'role_id' => $roleId,
                'model_type' => 'App\Models\User',
                'model_id' => $userId
            ]]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('model_has_roles')->delete();
    }
}
