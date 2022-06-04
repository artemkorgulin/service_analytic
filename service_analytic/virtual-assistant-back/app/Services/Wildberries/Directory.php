<?php

namespace App\Services\Wildberries;

use Illuminate\Support\Facades\Http;

class Directory
{

    private $supplierId;
    private $apiKey;

    private static $apiMethodUrls = [
        "getList" => "https://content-suppliers.wildberries.ru/ns/characteristics-configurator-api/content-configurator/api/v1/directory/get/list",
    ];

    /**
     * Directory constructor
     * @param string $supplierId
     * @param string $apiKey
     * @throws \Exception
     */
    public function __construct(string $supplierId = '', string $apiKey = '')
    {

        if ($supplierId) {
            $this->supplierId = $supplierId;
        }
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
        if (!$supplierId && !$this->supplierId) {
            throw new \Exception("Supplier Id initially must set!");
        }
        if (!$apiKey && !$this->apiKey) {
            throw new \Exception("Api Key initially must set!");
        }
    }

    /**
     * Get products
     */
    public function getList()
    {
        $response = Http::withHeaders([
            "Authorization" => $this->apiKey
        ])->retry(5, 30)->acceptJson()->get(self::$apiMethodUrls[__FUNCTION__]);
        return $response->object();
    }

}
