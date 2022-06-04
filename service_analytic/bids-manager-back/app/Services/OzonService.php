<?php

namespace App\Services;

use App\DataTransferObjects\AccountDTO;
use App\DataTransferObjects\Services\OzonPerformance\Client\TokenDTO;
use Cache;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;

class OzonService
{

    public const METHOD_GET = 'GET';
    public const METHOD_PUT = 'PUT';
    public const METHOD_POST = 'POST';

    protected static array $errors = [];

    protected ?AccountDTO $account;

    public ?TokenDTO $token = null;

    private GuzzleHttp\Client $httpClient;

    private Repository $cacheRepository;


    public function __construct()
    {
        $this->httpClient = new GuzzleHttp\Client();
    }

    /**
     * @return Repository
     * @warning somehow octaneTable isn't bound when running in a command
     * so laravel returns OctaneArrayStore that doesn't work properly as a cache repository
     */
    public function useOctaneCacheRepository(): Repository
    {
        $this->cacheRepository = Cache::store('octane');

        return $this->cacheRepository;
    }

    /**
     * @return Repository
     */
    public function useRedisCacheRepository(): Repository
    {
        $this->cacheRepository = Cache::store('redis_pool');

        return $this->cacheRepository;
    }

    /**
     * Получить последнюю ошибку
     * @return mixed
     */
    public static function getLastError()
    {
        return array_pop(static::$errors);
    }


    /**
     * Получить сообщение последней ошибки
     * @return mixed
     */
    public static function getLastErrorMessage()
    {
        $lastError = array_pop(static::$errors);

        return $lastError['message'];
    }


    /**
     * Запрос к Озону
     *
     * @param  string  $host
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $params
     *
     * @return bool|object
     */
    protected function send(string $host, string $method, string $uri, array $params = [])
    {
        $uri     = $host.$uri;
        $options = $this->getRequestOptions($params);

        try {
            Log::info(sprintf('Запрос к Ozon: %s %s', $method, $uri));
            $res = $this->httpClient->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            static::$errors[] = [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'method'  => $method,
                'uri'     => $uri,
                'request' => $options,
            ];
            Log::info('Ошибка подключения к Ozon', static::$errors);

            return false;
        }

        $answer = $res->getBody();

        if ($res->getStatusCode() == 200) {
            return $answer;
        }

        $result           = json_decode($answer);
        static::$errors[] = [
            'code'    => $result?->error?->code,
            'message' => $result?->error?->message,
            'method'  => $method,
            'uri'     => $uri,
            'request' => $options,
        ];
        Log::info('Ошибка запроса к Ozon', static::$errors);

        return false;
    }


    protected function getRequestOptions(array $params = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if (!is_null($this->token)) {
            $headers['Authorization'] = sprintf('%s %s', $this->token->token_type, $this->token->access_token);
        } elseif ($this->account->platform_client_id && $this->account->platform_api_key) {
            $headers['Client-Id'] = $this->account->platform_client_id;
            $headers['Api-Key']   = $this->account->platform_api_key;
        }

        $options = ['headers' => $headers];

        if ($params) {
            $options[RequestOptions::JSON] = $params;
        }

        return $options;
    }


    /*** Getters and setters ***/

    public function getAccount(): AccountDTO|null
    {
        return $this->account;
    }


    /**
     * @param  \App\DataTransferObjects\AccountDTO|null  $account
     *
     * @return void
     */
    public function setAccount(?AccountDTO $account): void
    {
        $this->account = $account;
    }


    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient(): GuzzleHttp\Client
    {
        return $this->httpClient;
    }


    /**
     * @param  \GuzzleHttp\Client  $httpClient
     */
    public function setHttpClient(GuzzleHttp\Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
