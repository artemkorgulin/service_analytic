<?php


namespace App\Services;

use App\Services\dist\permission\PermissionAccount;

class PermissionService
{
    public function bindPermissionsRole($roleId, $permissions){
        (new PermissionAccount())->setRoleId($roleId)->setPermissions($permissions)->change();
    }
}
