<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Class CheckRequestAccess
 * Check http request access
 *
 * @package   App\Http\Middleware
 * @author    Artem Korgulin <a.korgulin@yandex.ru>
 */
class CheckRequestAccess
{
    protected $openRoutes = [
        'login',
        'vt',
        'vt/*',
        'nova-api/*',
        'nova-vendor/*',
        'vp/download/*'
    ];

    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function handle($request, Closure $next)
    {
        if (!request()->is($this->openRoutes)) {
            return view('empty');
        }

        return $next($request);
    }
}
