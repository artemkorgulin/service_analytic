<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;
use Symfony\Component\HttpFoundation\Response;
use Closure;
use Illuminate\Http\Request;

class CheckUserAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $account = UserService::getCurrentAccount();

        if (!is_array($account)) {
            return response()->api_fail(
                __('middleware.check_user_account.fail_structure'),
                [$account],
                Response::HTTP_UNPROCESSABLE_ENTITY,
                AuthErrors::EMPTY_API_TOKEN
            );
        }

        if (!$account['platform_client_id']) {
            return response()->api_fail(
                __('middleware.check_user_account.fail_api_key'),
                [],
                Response::HTTP_UNPROCESSABLE_ENTITY,
                AuthErrors::EMPTY_API_TOKEN
            );
        }

        return $next($request);
    }
}
