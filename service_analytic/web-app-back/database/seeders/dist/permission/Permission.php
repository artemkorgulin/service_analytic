<?php

namespace Database\Seeders\dist\permission;


use App\Models\Role;
use Exception;

/**
 *
 */
class Permission implements IPermission
{
    /**
     * @var
     */
    private $permissions;

    /**
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @throws Exception
     */
    public function setJson($json){
        if(empty($json['permissions']))
            throw new Exception('не найдены роли');
        $this->permissions = $json['permissions'];
    }

    /**
     * Создаем права
     */
    public function build(){
        foreach($this->permissions as $permission){
            if(!\App\Models\Permission::query()->where(['name' => $permission['name']])->count())
                \App\Models\Permission::query()->create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name']
                ]);
        }
    }

}
