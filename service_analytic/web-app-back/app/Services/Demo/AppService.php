<?php

namespace App\Services\Demo;


class AppService
{
    public function isDemoServer()
    {
        return stripos(config('app.code'), 'demo') !== false;
    }
}
