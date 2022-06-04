<?php

namespace App\Services\Wildberries;

use App\Models\OzonProxy;
use GuzzleHttp\Client;

class MobileClientService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param int $nmid
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProductInfo(array $nmid): array
    {
        $arrayToStringNmid = implode(';', $nmid);
        $url = 'https://wbxcatalog-ru.wildberries.ru/nm-2-card/catalog?locale=ru&lang=ru&nm=' . $arrayToStringNmid;
        $proxy = OzonProxy::query()->inRandomOrder()->first();

        $response = $this->client->request(
            'GET',
            $url,
            [
                "proxy" => "http://$proxy->login:$proxy->password@$proxy->ip:$proxy->port_https"
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
