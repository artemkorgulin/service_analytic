<?php

namespace App\Helpers;

use Illuminate\Routing\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaHelper
{

    /**
     * Set {resource} route parameter to provided value
     * Use in custom versions of Laravel Nova controllers
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $resourceValue
     *
     * @return void
     */
    public static function setResourceParameter(NovaRequest $request, string $resourceValue): void
    {
        /** @var Route $route */
        $route = call_user_func($request->getRouteResolver());
        $route->setParameter('resource', $resourceValue);
        $request->setRouteResolver(fn() => $route);
    }
}
