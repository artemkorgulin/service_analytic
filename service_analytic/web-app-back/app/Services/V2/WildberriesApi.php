<?php

namespace App\Services\V2;

use App\Contracts\Api\WildberriesApiInterface;
use App\Services\ProxyService;
use Illuminate\Support\Facades\Http;

class WildberriesApi implements WildberriesApiInterface
{
    /** @var string $clientId Wildberries client id */
    private string $clientId;

    /** @var string $apiKey Wildberries apikey */
    private string $apiKey;

    /**
     * Constructor
     *
     * @param string $clientId
     * @param string $apiKey
     */
    public function __construct(string $clientId, string $apiKey)
    {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
    }

    /**
     * Check Api-key
     *
     * @return bool
     */
    public function validateAccessData(): bool
    {
        return $this->validRequest('wildberries/validate-access');
    }

    /**
     * Check Client Id
     *
     * @return bool
     */
    public function validateAccessDataForClientId(): bool
    {
        return $this->validRequest('wildberries/validate-access/client');
    }

    /**
     * Send request to virtual-assistant
     *
     * @param string $name
     * @return bool
     */
    private function validRequest(string $name): bool
    {
        $version = 'v2';
        $uri = 'vp/' . $version . '/' . $name;
        $configName = ProxyService::getConfigByUri($uri);
        $configKey = config(sprintf('%s.url%s', $configName, !empty($version) ? '_' . $version : ''));
        $url = sprintf('%s/%s', $configKey, $name);
        $response = Http::withHeaders([
            'Authorization-Web-App' => config($configName . '.token')
        ])->post($url, [
            'client_id' => $this->clientId,
            'api_key' => $this->apiKey
        ]);
        return (
            $response->status()
            && !empty($response->json()['success'])
            && $response->json()['success'] === true
        );
    }
}
