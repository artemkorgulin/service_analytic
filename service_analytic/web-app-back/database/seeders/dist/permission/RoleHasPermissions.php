<?php

namespace Database\Seeders\dist\permission;

use Illuminate\Support\Facades\DB;

class RoleHasPermissions implements IPermission
{

    private $roles;

    private $permissions;

    public function setJson($json)
    {
        $this->roles = $json['roles'];
        $this->permissions = $json['permissions'];
    }

    public function build()
    {
        foreach ($this->roles as $roleData) {
            $role = DB::table('roles')->where('name', '=', $roleData['name'])->first();
            foreach ($this->permissions as $permissionData) {
                if (!empty($permissionData[$role->name])) {
                    $permission = DB::table('permissions')->where('name', '=', $permissionData['name'])->first();
                    if (!\App\Models\RoleHasPermission::query()->where([
                        'permission_id' => $permission->id, 'role_id' => $role->id
                    ])->count()) {
                        \App\Models\RoleHasPermission::query()->create([
                            'permission_id' => $permission->id,
                            'role_id' => $role->id
                        ]);
                    }
                }
            }
        }
    }


}
