<?php


namespace Tests\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait UserForTest
{
    public static function getDataset()
    {
        $path = realpath('./tests/Env/dataset.php');
        $config = require $path;

        return $config;
    }

    /**
     * Get test user data
     *
     * @return Collection
     */
    public function getUser(): Collection
    {
        $config = self::getDataset();

        return new Collection([
            'id' => Arr::get($config, 'user_id'),
            'token' => Arr::get($config, 'token'),
            'accounts' => [
                'performance' => [
                    'id' => Arr::get($config, 'accounts.performance.id'),
                    'platform_client_id' => Arr::get($config, 'accounts.performance.platform_client_id'),
                    'platform_api_key' => Arr::get($config, 'accounts.performance.platform_api_key'),
                    'platform_id' => Arr::get($config, 'accounts.performance.platform_id'),
                    'title' => 'AnalyticPlatform Ozon Performance',
                    'is_active' => '1',
                    'platform_title' => Arr::get($config, 'platforms.performance.title'),
                    'pivot' => [
                        'user_id' => Arr::get($config, 'user_id'),
                        'account_id' => Arr::get($config, 'accounts.performance.id'),
                        'is_account_admin' => '0',
                        'is_selected' => '1',
                    ],
                    'platform' => Arr::get($config, 'platforms.performance'),
                ],
                'ozon' => [
                    'id' => Arr::get($config, 'accounts.ozon.id'),
                    'platform_client_id' => Arr::get($config, 'accounts.ozon.platform_client_id'),
                    'platform_api_key' => Arr::get($config, 'accounts.ozon.platform_api_key'),
                    'platform_id' => Arr::get($config, 'accounts.ozon.platform_id'),
                    'title' => 'CytiNature Ozon',
                    'is_active' => '1',
                    'platform_title' => Arr::get($config, 'platforms.ozon.title'),
                    'pivot' => [
                        'user_id' => Arr::get($config, 'user_id'),
                        'account_id' => Arr::get($config, 'accounts.ozon.id'),
                        'is_account_admin' => '0',
                        'is_selected' => '1',
                    ],
                    'platform' => Arr::get($config, 'platforms.ozon'),
                ]
            ],
        ]);
    }

    /**
     * Get user data array from request
     *
     * @return array
     */
    public function getRequestUserData(): array
    {
        $user = $this->getUser();

        return [
            'user' => $user,
            'accounts' => $user['accounts']
        ];
    }

    /**
     * Get request header
     *
     * @return array
     */
    public function getRequestHeader(): array
    {
        return [
            'Authorization-Web-App' => config('auth.web_app_token'),
            'Content-Type' => 'application/json'
        ];
    }
}

