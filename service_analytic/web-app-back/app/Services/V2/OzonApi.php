<?php


namespace App\Services\V2;

use App\Contracts\Api\OzonApiInterface;
use App\Traits\GuzzleResponseHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Class OzonApi
 * Сервис для отправки запросов в озон
 * @package App\Services\V2
 */
class OzonApi implements OzonApiInterface
{
    use GuzzleResponseHandler;

    /**
     * Url озона
     * @var mixed
     */
    private $host;

    /**
     * Guzzle клиент
     * @var Client
     */
    private $client;

    public function __construct($clientId, $apiKey)
    {
        $this->host = env('ozon_host_url');

        $stack = HandlerStack::create();
        $stack->push(new LogMiddleware(Log::channel('guzzle_request')));

        $this->client = new Client(['headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Client-Id' => $clientId,
            'Api-Key' => $apiKey,
        ], 'handler' => $stack]);
    }

    /**
     * Проверка Api-Key
     *
     * @return bool
     */
    public function validateAccessData(): bool
    {
        return $this->getCategoriesTree()['statusCode'] == 200;
    }

    /**
     * Получить список категорий
     *
     * @return array
     */
    public function getCategoriesTree(): array
    {
        return $this->formatResult(function () {
            return $this->client->request('GET', $this->host . '/v1/categories/tree');
        });
    }

    /**
     * Получить список характеристик для указанной категории
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategoryFeature(int $categoryId): array
    {
        return $this->formatResult(function () use ($categoryId) {
            return $this->client->request('GET', $this->host . '/v1/categories/' . $categoryId . '/attributes');
        });
    }

    /**
     * Получить список характеристик для указанной категории
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategoryFeatureV2(int $categoryId): array
    {
        return $this->formatResult(function () use ($categoryId) {
            return $this->client->request('POST', $this->host . '/v2/category/attribute', [
                'json' => [
                    'category_id' => $categoryId
                ]
            ]);
        });
    }

    /**
     * Получить список всех значений справочника категори
     *
     * @param int $categoryId
     * @param int $featureId
     * @param int $lastValueId
     * @return array
     */
    public function getCategoryFeatureOptions(int $categoryId, int $featureId, int $lastValueId = 0): array
    {
        return $this->formatResult(function () use ($categoryId, $featureId, $lastValueId) {
            return $this->client->request('POST', $this->host . '/v2/category/attribute/values', [
                'json' => [
                    'attribute_id' => $featureId,
                    'category_id' => $categoryId,
                    'limit' => 50,
                    'last_value_id' => $lastValueId,
                ]
            ]);
        });
    }

    /**
     * Получить информацию о товаре по его SKU
     *
     * @param int $productSku
     * @return array
     */
    public function getProductInfo(int $productSku): array
    {
        return $this->formatResult(function () use ($productSku) {
            return $this->client->request('POST', $this->host . '/v2/product/info', [
                'json' => [
                    'sku' => $productSku
                ]
            ]);
        });
    }

    /**
     * Получить характеристики товара по его offer_id
     *
     * @param array $offersId
     * @param int $pageSize
     * @param int $page
     * @return array
     */
    public function getProductFeatures(array $offersId, int $pageSize = 0, int $page = 0): array
    {
        return $this->formatResult(function () use ($offersId, $pageSize, $page) {
            return $this->client->request('POST', $this->host . '/v2/products/info/attributes', [
                'json' => [
                    'filter' => [
                        'offer_id' => $offersId
                    ],
                    'page' => $page,
                    'page_size' => $pageSize
                ]
            ]);
        });
    }

    /**
     * Отправить изменения по товарам на модерацию
     *
     * @param array $items
     * @return array
     */
    public function importProduct(array $items): array
    {
        return $this->formatResult(function () use ($items) {
            return $this->client->request('POST', $this->host . '/v2/product/import', [
                'json' => [
                    'items' => $items
                ]
            ]);
        });
    }
}
