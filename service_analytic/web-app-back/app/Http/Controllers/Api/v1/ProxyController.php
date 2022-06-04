<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ProxyService;
use Request;

/**
 * Class ProxyController
 * @package App\Http\Controllers\Api\v1
 */
class ProxyController extends Controller
{
    /**
     * Proxy request
     *
     * @param Request $request
     * @param string $version
     * @param string $name
     *
     * @return mixed
     */
    public function proxy(Request $request, string $version, string $name)
    {
        $uri = \request()->path();

        $configName = ProxyService::getConfigByUri($uri);

        $proxy = new ProxyService($configName, $version, $name);

        return $proxy->handle();
    }
}
