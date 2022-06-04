<?php

namespace Database\Seeders\dist\permission;



use App\Models\Role;
use Exception;

/**
 *
 */
class Roles implements IPermission
{

    /**
     * @var
     */
    private $roles;

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @throws Exception
     */
    public function setJson($json){
        if(empty($json['roles']))
            throw new Exception('не найдены роли');
        $this->roles = $json['roles'];
    }


    /**
     * Создаем роли
     */
    public function build()
    {

        foreach ($this->roles as $role) {
            if(!\App\Models\Role::query()->where(['name' => $role['name']])->count())
                \App\Models\Role::query()->create([
                    'name' => $role['name'],
                    'guard_name' => $role['guard_name']
                ]);
        }
    }

}
