<?php

namespace App\Http\Controllers;

use App\Services\ProxyService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ProxyWebController
{
    /**
     * Proxy to web
     *
     * @param  string  $name
     * @param  mixed  $id
     * @return Response
     */
    public function __invoke(string $name, mixed $id): Response
    {
        $uri = request()->path();
        $configName = ProxyService::getConfigByUri($uri);
        $url = str_replace('vp/', 'http://nginx-va/', $uri);
        $response = Http::withHeaders(['Authorization-Web-App' => config($configName.'.token')])->get($url);
        return \Response::make($response->body(), $response->status(), $response->headers());
    }
}
