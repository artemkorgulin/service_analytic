<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class UserService
 * @package App\Services
 */
class InnerService
{

    /**
     * @var string token for communication between services
     */
    protected string $authInnerToken;

    protected string $webAppApiUrl;


    /**
     * Client constructor.
     * The Loner constructor should always be hidden to prevent creating an object using the new operator.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->authInnerToken = config('auth.web_app_token', '');
        $this->webAppApiUrl = config('auth.web_app_api_url', '');

        if (!$this->webAppApiUrl) {
            throw new \Exception("Api WAB url must set!");
        }
        if (!$this->authInnerToken) {
            throw new \Exception("Api Key initially must set!");
        }
    }

    /**
     * Get all users and accounts
     * @return array|mixed
     */
    public function getAllUsersAndAccounts()
    {
        $url = "{$this->webAppApiUrl}/inner/get-all-users-and-accounts";
        try {
            $response = $this->commonAuthHeader()->get($url)->json();
        } catch (\Exception $exception) {
            report($exception);
            return [];
        }
        return $response;
    }

    /**
     * Get all platforms
     * @return array|mixed
     */
    public function getAllPlatforms()
    {
        $url = "{$this->webAppApiUrl}/inner/platforms";
        return $this->commonAuthHeader()->get($url)->json();
    }


    /**
     * Get all seller accounts for platform id, default ($platformId = 1)
     * @param int $platformId
     * @return array|mixed
     */
    public function getAllSellerAccounts(int $platformId = 1)
    {
        $platformId = "/{$platformId}";
        $url = "{$this->webAppApiUrl}/inner/vp-accounts{$platformId}";
        return $this->commonAuthHeader()->get($url)->json();
    }

    /**
     * Get account
     * @param $id
     * @return array|mixed
     */
    public function getAccount($id)
    {
        $url = "{$this->webAppApiUrl}/inner/accounts/{$id}";
        $response = $this->commonAuthHeader()->get($url);
        $account = $response->json();
        if ($response->status() === 200 && isset($account['platform_client_id']) && $account['platform_client_id'] ||
            isset($account['platform_api_key']) && $account['platform_api_key']) {
            return $account;
        }
        return false;
    }

    /**
     * Get user
     * @param $id
     * @return array|mixed
     */
    public function getUser($id)
    {
        $url = "{$this->webAppApiUrl}/inner/user/{$id}";
        $response = $this->commonAuthHeader()->get($url);
        if ($response->status() === 200) {
            return $response->json();
        }
        Log::warning("Warning! Can\'t get account with id={$id}");
        return false;
    }


    /**
     * Get user account for Ozon
     * @param $id
     * @return array|mixed
     */
    public function getUserAccount($id)
    {
        $url = "{$this->webAppApiUrl}/inner/user-oz-accounts/{$id}";
        return $this->commonAuthHeader()->get($url)->json();
    }

    /**
     * Common auth header
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function commonAuthHeader()
    {
        return Http::withHeaders([
            'Authorization-Web-App' => $this->authInnerToken,
            'Accept' => 'application/json; charset=utf-8'
        ]);
    }
}
