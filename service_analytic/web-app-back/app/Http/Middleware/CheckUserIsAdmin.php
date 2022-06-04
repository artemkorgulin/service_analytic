<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Session;

/**
 * Class CheckUserIsAdmin
 * Check admin access
 *
 * @package   App\Http\Middleware
 * @author    Artem Korgulin <a.korgulin@yandex.ru>
 */
class CheckUserIsAdmin
{
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
        if (Session::has('originalUser')) {
            $user = User::find(Session::get('originalUser'));
        } else {
            $user = User::getCurrentUser();
        }

        if (!$user->hasRole('admin')) {
            abort(403);
        }

        return $next($request);
    }
}
