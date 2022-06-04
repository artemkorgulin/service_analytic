<?php

namespace Database\Seeders;



use Database\Seeders\dist\permission\PermissionCreate;
use Database\Seeders\dist\permission\RoleHasPermissions;
use Database\Seeders\dist\permission\Users;
use Exception;
use Illuminate\Database\Seeder;
use \Database\Seeders\dist\permission\Roles;
use \Database\Seeders\dist\permission\Permission;

class AddPermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        (new PermissionCreate(new Roles(), new Permission(), new RoleHasPermissions(), new Users()))->run();
    }
}
