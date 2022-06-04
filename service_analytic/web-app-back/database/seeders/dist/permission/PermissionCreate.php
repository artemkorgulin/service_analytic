<?php

namespace Database\Seeders\dist\permission;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Создаем права пользователей
 */
class PermissionCreate
{
    /**
     * @var
     */
    private $jsonData;
    /**
     * @var
     */
    private $roles;
    /**
     * @var
     */
    private $permission;

    /**
     * @var
     */
    private $users;

    /**
     * @var
     */
    private $roleHasPermissions;

    /**
     * @return mixed
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this->users;
    }


    /**
     * @return PermissionCreate
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function setJsonData(): PermissionCreate
    {
        $json = \File::get(__DIR__."/../../data/data.json");

        if(!$json)
            throw new Exception('Не удалось получить json файл из папки ' . __DIR__ . DIRECTORY_SEPARATOR . 'data.json');
        $json = json_decode($json,true);

        if(json_last_error())
            throw new Exception(json_last_error_msg());

        $this->jsonData = $json;

        return $this;
    }

    /**
     * @param $role
     * @return mixed
     */
    protected function setRoles($role){
        $this->roles = $role;
        return $this->roles;
    }

    /**
     * @param $permission
     * @return mixed
     */
    protected function setPermission($permission){
        $this->permission = $permission;
        return $permission;
    }

    /**
     * @param $roleHasPermissions
     * @return mixed
     */
    protected function setRoleHasPermissions($roleHasPermissions){
        $this->roleHasPermissions = $roleHasPermissions;
        return $roleHasPermissions;
    }

    /**
     * @param IPermission $role
     * @param IPermission $permission
     * @param IPermission $roleHasPermissions
     * @param IPermission $users
     * @throws FileNotFoundException
     */
    public function __construct(IPermission $role, IPermission $permission, IPermission $roleHasPermissions, IPermission $users)
    {
        $this->setJsonData();
        $this->setRoles($role)->setJson($this->jsonData);
        $this->setPermission($permission)->setJson($this->jsonData);
        $this->setRoleHasPermissions($roleHasPermissions)->setJson($this->jsonData);
        $this->setUsers($users)->setJson($this->jsonData);
    }

    /**
     * @return $this
     */
    private function buildRole(): PermissionCreate
    {
        $this->roles->build();
        return $this;
    }

    /**
     * @return PermissionCreate
     */
    private function buildPermission(): PermissionCreate
    {
        $this->permission->build();
        return $this;
    }

    private function buildRoleHasPermissions(): PermissionCreate
    {
        $this->roleHasPermissions->build();
        return $this;
    }

    private function createPermission(){
        $this->users->build();
    }

    /**
     * Точка входа
     */
    public function run(){
        $this->buildRole()
            ->buildPermission()
            ->buildRoleHasPermissions()
            ->createPermission();
    }


}
