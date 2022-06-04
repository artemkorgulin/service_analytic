<?php

namespace App\Repositories\Wildberries;

use App\Services\Demo\AppService;
use App\Services\Wildberries\Client;
use App\Services\Wildberries\MobileClientService;
use App\Services\Wildberries\WildberriesAccountPricePlatformService;

class WildberriesPlatformProductRepository
{
    public function __construct(private MobileClientService $clientService)
    {
        //
    }

    /**
     * @param int $nmid
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProductPricesByNmid(array $nmid)
    {
        // Не нужно использовать прокси вне продакшен сервера, что бы прокси сохранились как можно дольше.
        if (!AppService::isProductionServer()) {
            return $this->getDemoData($nmid);
        }

        $response = $this->clientService->getProductInfo($nmid);

        if (!$response['data']['products']) {
            return [];
        }

        $productPriceArray = [];

        foreach ($response['data']['products'] as $product) {
            $productPriceArray[] = [
                'nmId' => $product['id'],
                'price' => $this->getFormattedPrice($product['salePriceU']),
                'discount' => (int) $product['sale'] ?? 0,
            ];
        }

        return $productPriceArray;
    }

    /**
     * @param int $account_id
     * @param array $nmids
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProductPricesFromNmidsFromFile(
        int   $account_id,
        array $nmids
    ) {
        $wbPricePlatformService = new WildberriesAccountPricePlatformService($account_id);
        return $wbPricePlatformService->getPriceFromFile($nmids);
    }

    /**
     * @param array $nmids
     * @return array
     */
    public function getDemoData(array $nmids)
    {
        $data = [];

        foreach ($nmids as $nmid) {
            $data[] = [
                'nmId' => $nmid,
                'price' => $this->getFormattedPrice(mt_rand(100, 5000)),
                'discount' => mt_rand(5, 70),
            ];
        }

        return $data;
    }

    /**
     * @param $price
     * @return string
     */
    public function getFormattedPrice($price): string
    {
        return  number_format(floatval($price), 2, '.', '');
    }
}
