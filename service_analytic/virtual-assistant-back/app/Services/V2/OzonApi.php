<?php


namespace App\Services\V2;

use App\Exceptions\Ozon\OzonApiException;
use App\Exceptions\Ozon\OzonServerException;
use App\Services\Ozon\OzonAnalyticsDataService;
use App\Services\UserService;
use App\Traits\GuzzleResponseHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\Handler\MultiRecordArrayHandler;
use GuzzleLogMiddleware\LogMiddleware;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OzonApi
{
    use GuzzleResponseHandler;

    private $host;
    private $client;

    private $clientId;
    private $apiKey;

    private $funcToUrl = [
        'getAnalyticsData' => 'analytics/data',
    ];

    public function __construct($clientId, $apiKey)
    {
        $this->host = config('env.ozon_host_url');

        $this->clientId = $clientId;
        $this->apiKey = $apiKey;

        $stack = HandlerStack::create();
        $stack->push(new LogMiddleware(Log::channel('guzzle_request'), new MultiRecordArrayHandler(null, 10000)));

        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Client-Id' => $clientId,
                'Api-Key' => $apiKey,
            ],
            'handler' => $stack
        ]);
    }

    /**
     * Проверка Api-Key
     *
     * @return bool
     */
    public function validateAccessData()
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
     * Создание товара
     * @param $item
     * @return array
     */
    public function createProduct($item): array
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return ['data' => [$item]];
        }

        $body = json_encode(['items' => [$item]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        Log::channel('import_request')->debug('Тело запроса создания товара: ' . $body);
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Client-Id' => $this->clientId,
            'Api-Key' => $this->apiKey,
        ])->post($this->host . '/v2/product/import', [
            'items' => [$item]
        ])->json();
    }

    /**
     * Узнать статус добавления товара
     * @param int $task_id
     * return array
     */
    public function getProductImportInfo($task_id)
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return ['result' => ['items' => [0 => ['status' => 'verified']]]];
        }

        Log::channel('import_request')->debug('Номер задачи по созданию товара в Ozon: ' . $task_id);
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Client-Id' => $this->clientId,
            'Api-Key' => $this->apiKey,
        ])->post($this->host . '/v1/product/import/info', ['task_id' => $task_id])->json();
    }

    /**
     * Получить список характеристик для указанной категории
     * @param int $categoryId
     * @return array
     * @deprecated
     *
     */
    public function getCategoryFeature(int $categoryId): array
    {
        return $this->formatResult(function () use ($categoryId) {
            return $this->client->request('GET', $this->host . '/v1/categories/' . $categoryId . '/attributes');
        });
    }


    /**
     * Получить список характеристик для указанной категории
     * This method was deprecated in Ozon don't use it anymore
     * Use please /v3/category/attribute
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
     * Получить список характеристик для указанной категории версия V3
     *
     * @param array of int $categoryId
     * @param string $attributeType Enum: "ALL" "REQUIRED" "OPTIONAL"
     * @return array
     */
    public function getCategoryFeatureV3(array $categoryIds, string $attributeType = "ALL"): array
    {
        return $this->formatResult(function () use ($categoryIds, $attributeType) {
            return $this->client->request('POST', $this->host . '/v3/category/attribute', [
                'json' => [
                    'category_id' => $categoryIds,
                    'attribute_type' => $attributeType
                ]
            ]);
        });
    }

    /**
     * Получить список всех значений справочника категории
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
                    'limit' => 1000,
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
            $response = $this->client->request('POST', $this->host . '/v2/product/info', [
                'json' => [
                    'sku' => $productSku
                ]
            ]);
            return $response;
        });
    }

    /**
     * Получить все продукты для пользователя
     * см. метод https://api-seller.ozon.ru/v1/product/list
     * https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetImportProductsInfo
     */
    public function getProductList($pagams)
    {
        return $this->formatResult(function () use ($pagams) {
            try {
                return $this->client->request('POST', $this->host . '/v1/product/list', [
                    'json' => $pagams
                ]);
            } catch (\Exception $exception) {
                report($exception);
                return [];
            }
        });
    }

    /**
     * Получить информацию по товарам по их SKU
     *
     * @param int $productSku
     * @return array
     */
    public function getProductInfoList(array $params): array
    {

        return $this->formatResult(function () use ($params) {
            $response = $this->client->request('POST', $this->host . '/v2/product/info/list', [
                'json' => $params
            ]);
            return $response;
        });
    }


    /**
     * Получить информацию о товаре по его product_id
     *
     * @param int $productId
     * @return array
     */
    public function getProductInfoById(int $productId): array
    {

        return $this->formatResult(function () use ($productId) {
            $response = $this->client->request('POST', $this->host . '/v2/product/info', [
                'json' => [
                    'product_id' => $productId
                ]
            ]);
            return $response;
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
    public function getProductFeatures(array $offersId, int $page = 0, int $pageSize = 0): array
    {
        return $this->formatResult(function () use ($offersId, $page, $pageSize) {
            try {
                return $this->client->request('POST', $this->host . '/v2/products/info/attributes', [
                    'json' => [
                        'filter' => [
                            'offer_id' => $offersId
                        ],
                        'page' => $page,
                        'page_size' => $pageSize
                    ]
                ]);
            } catch (\Exception $exception) {
                report($exception);
                return [];
            }
        });
    }


    /**
     * Получить характеристики товара по его offer_id
     *
     * @param array of int $offersId
     * @param int $pageSize
     * @param int $page
     * @return array
     */
    public function getProductFeaturesByProductId(array $productIds, int $page = 0, int $pageSize = 0): array
    {
        return $this->formatResult(function () use ($productIds, $page, $pageSize) {
            try {
                $response = $this->client->request('POST', $this->host . '/v2/products/info/attributes', [
                    'json' => [
                        'filter' => [
                            'product_id' => $productIds
                        ],
                        'page' => $page,
                        'page_size' => $pageSize
                    ]
                ]);
            } catch (\Exception $exception) {
                report($exception);
                return [];
            }
            return $response;
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
        if (config('api.disable_send_data_to_marketplaces')) {
            return ['data' => ['result' => ['task_id' => 0]]];
        }

        $body = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
        Log::channel('import_request')->debug('Тело запроса импорта: ' . $body);
        return $this->formatResult(function () use ($items) {
            return $this->client->request('POST', $this->host . '/v2/product/import', [
                'json' => [
                    'items' => $items
                ]
            ]);
        });
    }

    /**
     * Обновить цены
     *
     * @param array $prices
     * @return array
     */
    public function importPrices(array $prices): array
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return ['data' => ['result' => true]];
        }

        return $this->formatResult(function () use ($prices) {
            return $this->client->request('POST', $this->host . '/v1/product/import/prices', [
                'json' => [
                    'prices' => $prices
                ]
            ]);
        });
    }


    /**
     * Обновить цены
     *
     * @param array $prices
     * @return array
     */
    public function newImportPrices($data): array
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return ['data' => ['result' => true]];
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Client-Id' => $this->clientId,
            'Api-Key' => $this->apiKey,
        ])->post($this->host . '/v1/product/import/prices', $data)->json();
    }


    /**
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function curlImportPrices($data)
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return ['data' => ['result' => [0 => 889]]];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-seller.ozon.ru/v1/product/import/prices',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{"prices":[{"offer_id":"309-\\u041d\\u041c\\u041a-1","old_price":"889.00","premium_price":"587.00","price":"627.00","product_id":24624423}]}',
            CURLOPT_HTTPHEADER => array(
                'Client-Id: 1608',
                'Api-Key: 16c3c6e8-eead-40de-b61f-f6735e46448d',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        die();

    }


    /**
     * Вызывать метод озона с отдельной обработкой серверных (500) и клиентских ошибок
     * @param $method
     * @param ...$params
     * @return array
     * @throws OzonServerException
     */
    public function ozonRepeat($method, ...$params): array
    {
        $response = $this->repeat($method, ...$params);

        if (empty($response) || empty($response['statusCode'])) {
            return [];
        }

        if ($response['statusCode'] === 403) {
            $this->deactivateUsers();
            return [];
        }

        if (isset(OzonServerException::MESSAGES[$response['statusCode']]) || $response['statusCode'] >= 500) {
            throw new OzonServerException($response['statusCode']);
        }

        if ($response['statusCode'] !== 200) {
            throw new OzonApiException($response['statusCode']);
        }

        return $response;
    }

    /**
     * Deactivate users by current account
     */
    private function deactivateUsers()
    {
        if (config('app.env') !== 'testing') {
            UserService::deactivateUsersByClient($this->clientId, $this->apiKey, 'marketplace.ozon_api_keys_failed');
        }
    }

    /**
     * Get analytics data
     * @param $data_from
     * @param $data_to
     * @param $sku
     * @return array|mixed
     */
    public function getAnalyticsData($report_date, $sku)
    {
        $metrics = OzonAnalyticsDataService::getMetricNamesByReports();
        $responseArray = [];
        foreach ($metrics as $metric) {
            try {
                $response = Http::withHeaders([
                    'Client-Id' => (int)$this->clientId,
                    'Api-Key' => $this->apiKey,
                ])->post(config('env.ozon_api_seller_v1_url') . $this->funcToUrl[__FUNCTION__], [
                    'date_from' => $report_date,
                    'date_to' => $report_date,
                    'dimension' => ['sku', 'day'],
                    'filters' => [
                        [
                            'key' => 'sku',
                            'op' => 'EQ',
                            'value' => $sku,
                        ]
                    ],
                    'limit' => 50,
                    'metrics' => $metric,
                ])->json();
                $responseArray[] = $response;
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                report($exception);
                continue;
            }
        }
        return $responseArray;
    }

    /**
     * Get stock quantity
     * @param int $page
     * @param int $page_size
     * @return array
     */
    public function getStocks($page = 1, $page_size = 100)
    {
        return $this->formatResult(function () use ($page, $page_size) {
            try {
                return $this->client->request('POST', $this->host . '/v2/product/info/stocks', [
                    'json' => [
                        'page' => $page,
                        'page_size' => $page_size,
                    ]
                ]);
            } catch (\Exception $exception) {
                report($exception);
                return [];
            }

        });
    }
}
