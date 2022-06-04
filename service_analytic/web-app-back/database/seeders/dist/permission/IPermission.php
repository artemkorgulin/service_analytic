<?php

namespace Database\Seeders\dist\permission;

interface IPermission
{
    public function setJson($json);
    public function build();
}
