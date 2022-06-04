<?php


namespace App\Services\V2;


use App\Traits\GuzzleResponseHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Class OzonPerformanceApi
 * Сервис для работы с Ozon Performance
 * @package App\Services\V2
 */
class OzonPerformanceApi
{
    use GuzzleResponseHandler;

    /**
     * Url ozon performance
     * @var mixed
     */
    private $host;

    /**
     * Guzzle клиент
     * @var Client
     */
    private Client $client;

    /**
     * OzonPerformanceApi constructor.
     */
    public function __construct()
    {
        $this->host = env('ozon_performance_host_url');

        $stack = HandlerStack::create();
        $stack->push(new LogMiddleware(Log::channel('guzzle_request')));

        $this->client = new Client(['headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], 'handler' => $stack]);
    }

    /**
     * Валидация ключей для сервиса ozon performance
     * @param $clientId
     * @param $clientSecret
     * @return bool
     */
    public function validatePerformanceKeys($clientId, $clientSecret): bool
    {
        return $this->getPerformanceToken($clientId, $clientSecret)['statusCode'] == 200;
    }

    /**
     * Получение access токена от ozon performance
     * @param $clientId
     * @param $clientSecret
     * @return array
     */
    public function getPerformanceToken($clientId, $clientSecret): array
    {

        return $this->formatResult(function () use ($clientId, $clientSecret) {
            return $this->client->request(
                'POST',
                $this->host.'/api/client/token',
                [
                    \GuzzleHttp\RequestOptions::JSON => [
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'grant_type' => 'client_credentials'
                    ]
                ]
            );
        });
    }
}
