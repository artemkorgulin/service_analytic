<?php

namespace App\Http\Middleware;

use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;

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
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next) {
        $token = $request->header('Authorization-Web-App');
        // временно эту фигню всю вырубил,
        // так как есть необходимость вызывать с web-app-back
        // методы va там userа нет

//        $user = UserService::getUser();
//
//        Log::info($user);

//        if (empty($token) || $token !== config('auth.web_app_token') && (empty($user) && empty($user['id']))) {
        if (empty($token) || $token !== config('auth.web_app_token')) {
            Log::error('Неверный токен. Авторизация не удалась');
            return response()->api_fail(
                'Авторизация в Виртуальном помощнике не удалась',
                [],
                422,
                AuthErrors::EMPTY_API_TOKEN
            );
        }

        return $next($request);
    }
}
