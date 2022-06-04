<?php

namespace App\Services\Loaders;

use AnalyticPlatform\LaravelHelpers\Services\LoaderBaseService;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;

//todo: add output classes to use instead of echo (e.g. use adapter pattern for laravel command output)
abstract class LoaderService extends LoaderBaseService
{
    protected ?Client $client = null;

    protected ?Repository $cacheRepository = null;


    /**
     * @param  mixed  $account
     *
     * @return void
     */
    protected function outputAccountInfo(mixed $account): void
    {
        printf(
            <<<output
    account_id: %d
    platform_client_id: %s
    platform_api_key: %s

output,
            $account->id,
            $account->platform_client_id,
            Str::mask($account->platform_api_key, '*', 4, -10)
        );
    }

    /**
     * @param  \GuzzleHttp\Client  $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @return Repository
     */
    public function getCacheRepository(): Repository
    {
        return $this->cacheRepository;
    }

    /**
     * @param  Repository  $cacheRepository
     */
    public function setCacheRepository(Repository $cacheRepository): void
    {
        $this->cacheRepository = $cacheRepository;
    }
}
