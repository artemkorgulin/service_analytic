<?php

namespace App\Services\Demo;

class AppService
{
    /**
     * @return bool
     */
    public static function isProductionServer()
    {
        return stripos(config('app.code'), 'production') !== false;
    }
}
