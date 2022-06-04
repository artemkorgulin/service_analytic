<?php

namespace App\Services;

use App\Models\BlackListBrand;
use App\Models\OzProduct;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{


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
     * Получение всех уч. записей для текущего пользователя
     *
     * @return array|null
     */
    public static function getAccounts(): ?array
    {
        if (isset(request()->account)) {
            return [request()->account];
        }
        return null;
    }

    /**
     * Get current user account id
     * @return int|null
     */
    public static function getAccountId(): ?int
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccount();
        return isset($account['id']) ? (int)$account['id'] : null;
    }

    /**
     * Получение данных по текущему аккаунту
     *
     * @return array|null
     */
    public static function getCurrentAccount(): ?array
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }

        return request()->account ?? null;
    }

    /**
     * Get current user account platform_id
     *
     * @return int|null
     */
    public static function getCurrentAccountPlatformId()
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccount();

        return isset($account['platform_id']) ? (int)$account['platform_id'] : null;
    }

    /**
     * Get current user account platform_client_id
     *
     * @return int|null
     */
    public static function getCurrentAccountPlatformClientId()
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccount();

        return isset($account['platform_client_id']) ? (int)$account['platform_client_id'] : null;
    }

    /**
     * Get current user account platform_api_key
     *
     * @return int|null
     */
    public static function getCurrentAccountPlatformApiKey()
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccount();

        return $account['platform_api_key'] ?? null;
    }

    /**
     * Get current user account id
     *
     * @return int|null
     */
    public static function getCurrentAccountOzonId(): ?int
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccountOzon();
        return isset($account['id']) ? (int)$account['id'] : null;
    }

    /**
     * Получение данных по текущему аккаунту
     *
     * @return array|null
     */
    public static function getCurrentAccountOzon(): ?array
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccount();
        if (isset($account['platform']['title']) && $account['platform']['title'] === 'Ozon') {
            return $account;
        }
        return null;
    }

    /**
     * Get current user account id
     * @return int
     */
    public static function getCurrentAccountWildberriesId(): ?int
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccountWildberries();
        return isset($account['id']) ? (int)$account['id'] : null;
    }

    /**
     * Получение данных по текущему аккаунту
     *
     * @return array|null
     */
    public static function getCurrentAccountWildberries(): ?array
    {
        if (app()->runningInConsole() !== false) {
            return null;
        }
        $account = self::getCurrentAccount();

        if (isset($account['platform']['title']) && $account['platform']['title'] === 'Wildberries') {
            return $account;
        }
        return null;
    }

    /**
     * Получение аккаунта пользователя по его id
     * @param $id
     * @return array|mixed
     */
    public static function getAccount($id)
    {
        $bearerToken = self::auth();
        $url = env('WEB_APP_API_URL', '') . '/v1/accounts/' . $id;
        return Http::withToken($bearerToken)->get($url)->json();
    }

    /**
     * Авторизация с получением Bearer для пользователя
     */
    public static function auth(): ?string
    {
        $url = config('api.web_app_api_url') . '/v1/sign-in';
        $response = Http::post($url, [
            'email' => env('GOD_USER_NAME', ''),
            'password' => env('GOD_USER_PASSWORD', ''),
        ])->json();

        if (isset($response['user'])) {
            if (Str::startsWith($response['user'], 'bearer ') ||
                Str::startsWith($response['user'], 'Bearer ')) {
                return Str::substr($response['user'], 7);
            }
        }
        return null;
    }

    /**
     * Check user permission
     *
     * @param string $permission
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
     * @return bool
     */
    public static function canCreateProduct()
    {
        // Пока убрали тестовые продукты так как у пользователя почему-то is_test для продуктов всегда 0 (пока, оставил ТОЛЬКО продукты
//        $userTestProductsCount = OzProduct::where([['is_test', '=', true], ['user_id', '=', self::getUserId()]])->count();
        $userTestProductsCount = OzProduct::where(['user_id' => UserService::getUserId()])->count();
        if (self::hasBetaTariff() || $userTestProductsCount < 3) {
            return true;
        }

        return false;
    }

    /**
     * Get current user id
     *
     * @return int
     */
    public static function getUserId(): ?int
    {
        return (request()->user) ? (int)request()->user['id'] : null;
    }


    /**
     * Get max count for product related with active products
     * @return int
     */
    public static function getMaxProductsCount(): int
    {
        $tariff = request()->tariff ?? null;

        return intval($tariff['sku']) ?? 0;
    }

    /**
     * Return black list brand if user not partner
     * @return array
     */
    public static function forbiddenBrands(): array
    {
        if (isset(request()->permissions) && !in_array('brand.store_without_restrictions', request()->permissions)) {
            $blackListBrands = Cache::get('blackListBrands');
            if (!$blackListBrands) {
                $blackListBrands = BlackListBrand::select('brand')->pluck('brand')->toArray();
                Cache::add('blackListBrands', $blackListBrands, 1200);
            }
            return $blackListBrands;
        } else {
            return [];
        }
    }

    /**
     * Получение аккаунта пользователя по его id
     * @param $id
     * @return array|mixed
     */
    public static function loadAccount($id)
    {
        $url = env('WEB_APP_API_URL', '') . '/inner/accounts/' . $id;

        try {
            return Http::withHeaders([
                'Authorization-Web-App' => config('auth.web_app_token', ''),
                'Accept' => 'application/json; charset=utf-8'
            ])->get($url)->json();
        } catch (\Exception $exception) {
            report($exception);
            Log::error('Ошибка получения аккаунтов ' . $exception->getMessage());
        }

        return false;
    }

    /**
     * Get user sku
     */
    public static function getSku(): int
    {
        return (int)request()->max_sku_count ?? 0;
    }


    /**
     * Get SKU for Escrow
     *
     * @return array
     */
    public static function getEscrowSku(): int
    {
        return request()->max_escrow_count ?? 0;
    }

    /**
     * Deactivate users by client (client_id and api_key)
     *
     * @param string $clientId
     * @param string $apiKey
     * @param string $notificationCode
     */
    public static function deactivateUsersByClient(
        string $clientId,
        string $apiKey = '',
        string $notificationCode = ''
    ): void
    {
        $accountsAndUsers = Http::withHeaders([
            'Authorization-Web-App' => config('auth.web_app_token'),
            'Accept' => 'application/json; charset=utf-8'
        ])->post(
            config('api.web_app_api_url') . '/inner/accounts/' . $clientId . '/users/',
            ['api_key' => $apiKey]
        )->json();
        if (!empty($accountsAndUsers)) {
            foreach ($accountsAndUsers as $row) {
                $account = $row['account'] ?? null;
                $users = $row['users'] ?? [];
                if (!empty($account) && !empty($users) && !empty($notificationCode)) {
                    foreach ($users as $user) {
                        UsersNotification::dispatch(
                            $notificationCode,
                            [['id' => $user['id'], 'lang' => 'ru', 'email' => $user['email']]],
                            ['account' => $account['title']]
                        );
                    }
                }
            }
        }
    }
}
