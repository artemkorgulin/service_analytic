<?php

namespace App\Http\Middleware;

use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;
use App\Services\UserService;
use Closure;

/**
 * Class WebAppAuth
 * Проверка запросов от сервера Web-app
 *
 * @package App\Http\Middleware
 */
class WebAppAuth
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $token = $request->header('Authorization-Web-App');
        $user = UserService::getUser();

        if (empty($token) || $token !== config('auth.web_app_token') || empty($user) || empty($user['id'])) {
            return response()->api_fail(
                'Авторизация в ADM не удалась',
                [],
                422,
                AuthErrors::EMPTY_API_TOKEN
            );
        }

        return $next($request);
    }
}
