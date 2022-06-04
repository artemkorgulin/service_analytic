<?php

namespace App\Repositories\Ozon;

use App\Services\V2\OzonApi;

class OzonPlatformProductRepository
{
    /**
     * @param mixed $clientId
     * @param string $apiKey
     */
    public function __construct(mixed $clientId, string $apiKey)
    {
        $this->ozonApiClient = new OzonApi($clientId, $apiKey);
    }

    /**
     * @param array $externalIds
     * @return array
     */
    public function getProductsPriceByExternalIds(array $externalIds): array
    {
        $prices = [];
        $response = $this->ozonApiClient->repeat('getProductInfoList', ['product_id' => $externalIds]);

        if (empty($response['data']['result']['items'])) {
            return [];
        }

        foreach ($response['data']['result']['items'] as $item) {
            $prices[$item['id']] = [
                'external_id' => $item['id'],
                'price' => number_format(floatval($item['price']), 2, '.', '') ?? 0,
                'old_price' =>  number_format(floatval($item['old_price']), 2, '.', '') ?? 0,
            ];
        }

        return $prices;
    }
}
