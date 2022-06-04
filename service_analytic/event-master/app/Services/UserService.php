<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{
    const PLATFORM_ID = 2;

    /**
     * Авторизация с получением Bearer для пользователя
     */
    public static function auth(): ?string
    {
        $url = env('WEB_APP_API_URL', '').'/v1/sign-in';
        $response = Http::post($url , [
            'email' => env('GOD_USER_NAME', ''),
            'password' => env('GOD_USER_PASSWORD', ''),
        ])->json();
        if (isset($response['user']) && (Str::startsWith($response['user'], 'bearer ') ||
            Str::startsWith($response['user'], 'Bearer '))) {
            return Str::substr($response['user'], 7);
        }
        return null;
    }

    /**
     * Get current user data
     *
     * @return array
     */
    public static function getUser(): array
    {
        return request()->user ?? [];
    }

    /**
     * Get current user id
     *
     * @return int
     */
    public static function getUserId(): ?int
    {
        if (empty(request()->get('user')) || empty(request()->get('user')['id'])) {
            return null;
        }

        return (int) request()->get('user')['id'];
    }

    /**
     * Получение данных по текущему аккаунту
     *
     * @return array
     */
    public static function getCurrentAccount(): ?array
    {
        $accounts = request()->accounts;
        if (!request()->accounts) {
            abort('401');
        }
        foreach ($accounts as $account) {
            if ($account['pivot']['is_selected']) {
                return $account;
            }
        }
        return null;
    }


    /**
     * Получение всех уч. записей для текущего пользователя
     *
     * @return mixed
     */
    public static function getAccounts()
    {
        return request()->accounts;
    }

    /**
     * Get current user account id
     * @return int|null
     */
    public static function getAccountId(): ?int
    {
        $account = self::getCurrentAccount();

        if (empty($account) || empty($account['id'])) {
            return null;
        }

        return (int) $account['id'];
    }

    /**
     * Получение аккаунта пользователя по его id
     * @param $id
     * @return array|mixed
     */
    public static function getAccount($id)
    {
        $bearerToken = self::auth();
        $url = env('WEB_APP_API_URL', '') . '/v1/accounts/'.$id;
        return Http::withToken($bearerToken)->get($url)->object();
    }

    /**
     * Check user permission
     *
     * @param  string  $permission
     *
     * @return bool
     */
    public static function can(string $permission): bool
    {
        $permissionList = request()->permissions;

        if (empty($permissionList) || !is_array($permissionList)) {
            return false;
        }

        return in_array($permission, $permissionList);
    }


    /**
     * Get all accounts from current user
     *
     * @return array
     */
    public static function getAllAccounts(): array
    {
        $accounts  = [];
        if (isset(request()->accounts)  && count(request()->accounts) > 0){
            foreach (request()->accounts as $account) {
                if ($account['platform_id'] != UserService::PLATFORM_ID)
                    continue;
                $accounts[] = $account;
            }
        }

        return  $accounts;
    }

    /**
     * Get current account from current user
     *
     * @return int|null
     */
    public static function getCurrentAccountId(): ?int
    {
        $accounts = self::getAllAccounts();
        if ($accounts > 0){
            foreach ($accounts as $account) {
                if ($account['pivot']['is_selected'] == 1)
                 return   $account['id'];;
            }
        }
           return  null;
    }

}
