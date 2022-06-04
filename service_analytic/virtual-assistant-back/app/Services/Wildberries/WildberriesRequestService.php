<?php

namespace App\Services\Wildberries;

use App\Services\UserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WildberriesRequestService
{
    /** @var string */
    private string $supplierId;

    /** @var string */
    private string $apiKey;

    /** @var int */
    private int $repeats;

    /** @var int */
    private int $repeatInterval;

    /** @var int */
    private int $timeout;

    /** @var bool */
    private bool $useOnFail;

    /** @var string[] */
    private array $headers;

    /** @var string[] */
    private array $allowedMethods = ['get', 'post'];

    /** @var string */
    private string $logChannelRequest = 'wildberries_requests';

    /** @var string */
    private string $logChannelResponse = 'wildberries_responses';

    /**
     * WildberriesRequestService constructor.
     *
     * @param string $supplierId
     * @param string $apiKey
     * @param int $repeats
     * @param int $repeatInterval
     * @param int $timeout
     * @param bool $useOnFail
     */
    public function __construct(
        string $supplierId,
        string $apiKey,
        int $repeats = 1,
        int $repeatInterval = 0,
        int $timeout = 0,
        bool $useOnFail = true
    )
    {
        $this->supplierId = $supplierId;
        $this->apiKey = $apiKey;
        $this->repeats = $repeats;
        $this->repeatInterval = $repeatInterval;
        $this->timeout = $timeout;
        $this->useOnFail = $useOnFail;
        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => $this->apiKey,
            'Cache-Control' => 'no-cache',
            'User-Agent' => 'Mozilla Chrome Safari'
        ];
    }

    /**
     * Send request and return response
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @param bool $forceNotDisable
     * @return mixed
     * @throws \Exception
     */
    public function send(string $method, string $url, array $params = [], bool $forceNotDisable = false): mixed
    {
        $response = null;
        if (($forceNotDisable || !$this->isDisable()) && $this->isValidMethod($method)) {
            $this->logRequest($url . ': ' . print_r($params, true));
            for ($try = 1; $try <= $this->repeats; $try++) {
                $response = Http::withHeaders($this->headers)->withOptions(['verify' => false]);
                if (!empty($this->timeout)) {
                    $response->timeout($this->timeout);
                }
                if ($method === 'get') {
                    $response = $response->get($url, $params);
                } elseif ($method === 'post') {
                    $response = $response->post($url, $params);
                }
                if ($response->failed()) {
                    if ($try === $this->repeats) {
                        if ($this->useOnFail) {
                            $this->onFail($response);
                        }
                        $this->logError($url . ': ' . $response->body());
                        $response = null;
                    } else {
                        sleep($this->repeatInterval);
                    }
                    continue;
                }
                if ($response->successful()) {
                    $this->logResponse($url . ': ' . json_encode($response->object(), true));
                    break;
                }
            }
        }
        return $response;
    }

    /**
     * Is method valid
     *
     * @param string $method
     * @return bool
     * @throws \Exception
     */
    private function isValidMethod(string $method): bool
    {
        if (empty($method)) {
            throw new \Exception('Undefined method for request');
        }
        if (!in_array($method, $this->allowedMethods)) {
            throw new \Exception('Not allowed method for this request');
        }
        return in_array($method, $this->allowedMethods);
    }

    /**
     * Is sending disable
     *
     * @return bool
     */
    private function isDisable(): bool
    {
        return config('api.disable_send_data_to_marketplaces') ? true : false;
    }

    /**
     * Actions on fail request
     *
     * @param $response
     */
    private function onFail($response)
    {
        switch ($response->status()) {
            case 400:
                if (!stristr($response->body(), 'все номенклатуры с ценами из списка уже загружены')) {
                    $response->throw();
                }
                break;

            case 401:
            case 403:
                // invalid token / unauthorized
                //+ response Wildberries with status 403 message: "На данный момент пользователь заблокирован"
                $this->deactivateUsers();
                break;

            case 504:
                // @TODO - service unavailable
                break;

            default:
                // throw error by default
                $response->throw();
        }
    }

    /**
     * Add to log
     *
     * @param string $channel
     * @param string $data
     */
    private function log(string $channel, string $data, bool $isError = false)
    {
        if (config('app.env') !== 'testing') {
            if ($isError) {
                Log::channel($channel)->error($data);
            } else {
                Log::channel($channel)->info($data);
            }
        }
    }

    /**
     * Log request
     *
     * @param string $data
     */
    private function logRequest(string $data): void
    {
        $this->log($this->logChannelRequest, $data);
    }

    /**
     * Log response
     *
     * @param string $data
     */
    private function logResponse(string $data): void
    {
        $this->log($this->logChannelResponse, $data);
    }

    /**
     * Log response error
     *
     * @param string $data
     */
    private function logError(string $data): void
    {
        $this->log($this->logChannelResponse, $data, true);
    }

    /**
     * Deactivate users by current account
     */
    private function deactivateUsers()
    {
        if (config('app.env') !== 'testing') {
            UserService::deactivateUsersByClient($this->supplierId, $this->apiKey, 'marketplace.wb_api_keys_failed');
        }
    }
}
