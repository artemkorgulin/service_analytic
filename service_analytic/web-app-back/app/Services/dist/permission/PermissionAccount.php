<?php


namespace App\Services\dist\permission;


use App\Models\Permission;
use App\Models\RoleHasPermission;

class PermissionAccount
{
    /**
     * @var int
     */
    private int $roleId;

    private array $permissions;


    public function setRoleId($roleId): PermissionAccount
    {
        $this->roleId = $roleId;
        return $this;
    }

    public function setPermissions($permissions): PermissionAccount
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function change(){
        foreach ($this->permissions as $name => $flag) {
            $permission = Permission::query()->where(['name' => $name])->get()->pop();

            if($flag == 'true'){
                //insertOrIgnore
                RoleHasPermission::query()->insertOrIgnore([
                    'role_id' => $this->roleId,
                    'permission_id' => $permission->id
                ]);
            } else {
                RoleHasPermission::query()->where(['role_id' => $this->roleId, 'permission_id' => $permission->id])->delete();
            }
        }
    }
}
