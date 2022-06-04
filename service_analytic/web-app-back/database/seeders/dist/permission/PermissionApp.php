<?php

namespace Database\Seeders\dist\permission;

class PermissionApp
{
    protected $object;

    public function setObject(IPermission $object){
        $this->object = $object;
    }
}
