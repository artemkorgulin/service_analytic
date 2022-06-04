<?php

namespace App\Services\Wildberries;

use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;

class Client
{
    /** @var string */
    private string $supplierId;

    /** @var string */
    private string $apiKey;

    /** @var int */
    private int $repeats = 5;

    /** @var int */
    private int $repeatInterval = 3;

    /** @var int */
    private int $timeout = 300;

    /** @var bool */
    private bool $useOnFail = true;

    /** @var string */
    private string $apiUrl = 'https://suppliers-api.wildberries.ru';

    /** @var array */
    public array $apiMethodUrls = [
        "getCardList" => "/card/list",
        "setPrices" => "/public/api/v1/prices",
        "getInfo" => "/public/api/v1/info",
        "getCardByImtId" => "/card/cardByImtID",
        "cardCreate" => "/card/create",
        "cardUpdate" => "/card/update",
        "cardDeleteNomenclature" => "/card/deleteNomenclature",
        "cardGetBarcodes" => "/card/getBarcodes",
        "getObjectList" => "/api/v1/config/get/object/list",
        "getDirectoryExt" => "/api/v1/directory/ext",
        "getDirectoryList" => "/api/v1/directory/get/list",
        "getDirectoryColors" => "/api/v1/directory/colors",
        "updateDiscounts" => "/public/api/v1/updateDiscounts",
        "revokeDiscounts" => "/public/api/v1/revokeDiscounts",
        "updatePromocodes" => "/public/api/v1/updatePromocodes",
        "revokePromocodes" => "/public/api/v1/revokePromocodes",
        "getStocks" => "/api/v2/stocks", // get stocks info
    ];

    /** @var WildberriesRequestService */
    private WildberriesRequestService $requestService;

    /**
     * Client constructor
     *
     * @param string $supplierId
     * @param string $apiKey
     * @param int $repeats
     * @param bool $useOnFail
     * @throws \Exception
     */
    public function __construct(
        string $supplierId = '',
        string $apiKey = '',
        int    $repeats = 0,
        bool   $useOnFail = true
    )
    {
        $this->supplierId = $supplierId ?? '';
        $this->apiKey = $apiKey ?? '';
        if (!empty($repeats)) {
            $this->repeats = $repeats;
        }
        $this->useOnFail = $useOnFail;
        if (empty($this->supplierId)) {
            throw new \Exception("Supplier Id initially must set!");
        }
        if (empty($this->apiKey)) {
            throw new \Exception("Api Key initially must set!");
        }
        foreach ($this->apiMethodUrls as $key => $method) {
            $this->apiMethodUrls[$key] = $this->apiUrl . $method;
        }
        $this->requestService = new WildberriesRequestService(
            $this->supplierId,
            $this->apiKey,
            $this->repeats,
            $this->repeatInterval,
            $this->timeout,
            $this->useOnFail
        );
    }

    /**
     * Get products
     *
     * @param int $limit
     * @param int $offset
     * @param bool $onlyTotalProducts
     * @return mixed
     * @throws \Exception
     */
    public function getCardList(int $limit = 500, int $offset = 0, bool $onlyTotalProducts = false): mixed
    {
        $params = [
            "id" => time(),
            "jsonrpc" => "2.0",
            "params" => [
                "isArchive" => true,
                "query" => [
                    "limit" => $limit,
                    "offset" => $offset,
                    "total" => 0
                ],
                "supplierID" => $this->supplierId,
                "withError" => false
            ]
        ];

        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params, true);

        if (empty($response)) {
            return [];
        }

        if (isset($response->json()['result']['cards'])) {
            if ($onlyTotalProducts) {
                return (int)$response->json()['result']['cursor']['total'];
            }
            return $response->json()['result']['cards'];
        }

        return $response;
    }

    /**
     * Get prices
     * look https://suppliers-api.wildberries.ru/swagger/index.html#/%D0%A6%D0%B5%D0%BD%D1%8B/get_public_api_v1_info
     * for quantaty 2 - товар с нулевым остатком, 1 - товар с ненулевым остатком, 0 - товар с любым остатком
     * Look task SE-2224 (https://youtrack.korgulin.ru/issue/SE-2224)
     * Если WB возвращает {"errors":["на данный момент доступна только выгрузка данных с quantity = 0"]}
     * не нужно ничего делать, так как далее остатки всё равно у нас НЕ ХРАНЯТСЯ ПО ДАТАМ для товаров FBS
     * а только по номенклатурам есть или нет в наличии
     * @param int $quantity
     * @return array|null
     * @throws \Exception
     */
    public function getInfo(int $quantity = 0): ?array
    {
        $params = ['quantity' => $quantity ?? 0];
        $response = $this->requestService->send('get', $this->apiMethodUrls[__FUNCTION__], $params, true);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Get json for specific url
     * @param $url
     * @return \Psr\Http\Message\StreamInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getJsonStream($url, $method = 'GET', $json = null): ?\Psr\Http\Message\StreamInterface
    {
        $newWbClient = new GuzzleClient();

        $NUM_OF_ATTEMPTS = 5;
        $attempts = 0;
        $params = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $this->apiKey,
            ]
        ];

        if ($json) {
            $params = array_merge($params, ['json' => $json]);
        }

        do {
            try {
                $response = $newWbClient->request($method, $url, $params);
            } catch (\Exception $e) {
                $attempts++;
                $response = null;
                sleep(10);
                continue;
            }
            break;

        } while ($attempts < $NUM_OF_ATTEMPTS);

        if (!$response) {
            return null;
        }
        return $response->getBody();
    }

    /**
     * Set prices
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function setPrices(array $params): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Update discounts
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function updateDiscounts(array $params): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Update promocodes
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function updatePromocodes(array $params): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Обнуление скидок
     * Внимание это обнуление скидок! Это не установка!
     * см документацию https://suppliers-api.wildberries.ru/swagger/index.html#/%D0%9F%D1%80%D0%BE%D0%BC%D0%BE%D0%BA%D0%BE%D0%B4%D1%8B%20%D0%B8%20%D1%81%D0%BA%D0%B8%D0%B4%D0%BA%D0%B8/post_public_api_v1_revokeDiscounts
     * передаем в параметры ТОЛЬКО nm_id в виде [ 12345678, 81234567 ]
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function revokeDiscounts(array $params): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Update discount
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function revokePromocodes(array $params): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Get product card list by imtID
     *
     * @param int $imtId
     * @return array
     * @throws \Exception
     */
    public function getCardByImtId(int $imtId): array
    {
        $params = [
            "id" => time(),
            "jsonrpc" => "2.0",
            "params" => [
                "imtID" => $imtId,
                "supplierID" => $this->supplierId,
            ]
        ];
        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params, true);
        return !empty($response) ? $response->json() : [];
    }

    /**
     * Delete nomenclature in card
     *
     * @param $nomenclatureId
     * @return mixed
     * @throws \Exception
     */
    public function cardDeleteNomenclature($nomenclatureId): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        $params = [
            "id" => time(),
            "jsonrpc" => "2.0",
            "params" => [
                "nomenclatureID" => $nomenclatureId,
                "supplierID" => $this->supplierId,
            ]
        ];
        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Create card (product)
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function cardCreate(array $params): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        return $this->cardCreateOrUpdate($this->apiMethodUrls[__FUNCTION__], $params);
    }

    /**
     * Update and create method
     *
     * @param string $url
     * @param $params
     * @param $user
     * @return mixed
     * @throws \Exception
     */
    private function cardCreateOrUpdate(string $url, &$params, $user = null): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        if (is_string($params)) {
            $params = json_decode($params, true);
        }

        if (is_object($params)) {
            $params = (array)$params;
        }

        $preparedParams = [];
        $preparedParams['params']['card'] = $params;
        $preparedParams['id'] = time();
        $preparedParams['jsonrpc'] = "2.0";
        $preparedParams['supplierID'] = $this->supplierId;

        $response = $this->requestService->send('post', $url, $preparedParams);
        try {
            if ($user && isset($user['id']) && isset($user['email'])) {
                if (isset($response) && method_exists($response, 'object') && property_exists($response->object(), 'result') && empty((array)$response->object()->result)) {
                    UsersNotification::dispatch(
                        'card_product.marketplace_product_start_upload_success',
                        [['id' => $user['id'], 'lang' => 'ru', 'email' => $user['email']]],
                        ['marketplace' => 'WB']
                    );
                } else {
                    UsersNotification::dispatch(
                        'card_product.marketplace_product_start_upload_fail',
                        [['id' => $user['id'], 'lang' => 'ru', 'email' => $user['email']]],
                        ['marketplace' => 'WB']
                    );
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Cart update
     *
     * @param $params
     * @param null $user
     * @return mixed
     * @throws \Exception
     */
    public function cardUpdate($params, $user = null): mixed
    {
        if (config('api.disable_send_data_to_marketplaces')) {
            return new stdClass();
        }

        if (isset($params->data_nomenclatures)) {
            unset($params->data_nomenclatures);
        }

        if (isset($params->params)) {
            unset($params->params);
        }

        return $this->cardCreateOrUpdate($this->apiMethodUrls[__FUNCTION__], $params, $user);
    }

    /**
     * Card create barcodes for sizeable products
     *
     * @param int $qty
     * @return mixed
     * @throws \Exception
     */
    public function cardGetBarcodes($qty = 1): mixed
    {
        $params['id'] = time();
        $params['jsonrpc'] = "2.0";
        $params['params']['quantity'] = $qty;
        $params['params']['supplierID'] = $this->supplierId;
        $response = $this->requestService->send('post', $this->apiMethodUrls[__FUNCTION__], $params);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Get object list
     * Use for get parent
     *
     * @param $params
     * @return object|null
     * @throws \Exception
     */
    public function getObjectList(array $params): ?object
    {
        if (!isset($params['pattern']) || !$params['pattern']) {
            return null;
        }
        if (!isset($params['lang']) || !$params['lang']) {
            $params['lang'] = 'ru';
        }
        $response = $this->requestService->send('get', $this->apiMethodUrls[__FUNCTION__], $params, true);
        return !empty($response) ? $response->object() : null;
    }

    /**
     * Получение значений из справочника Ext
     *
     * @param $title
     * @return mixed
     * @throws GuzzleException
     */
    public function getDirectoryExt($title): mixed
    {
        $url = $this->apiMethodUrls[__FUNCTION__];
        $newWbClient = new GuzzleClient();
        $NUM_OF_ATTEMPTS = 5;
        $attempts = 0;
        do {
            try {
                $response = $newWbClient->request('GET', $url . '?option=' . rawurlencode($title), [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $this->apiKey,
                    ]
                ]);
            } catch (\Exception $e) {
                $attempts++;
                $response = null;
                sleep(10);
                continue;
            }
            break;
        } while ($attempts < $NUM_OF_ATTEMPTS);
        return !empty($response) ? $response->getBody() : null;
    }

    /**
     * Получение произвольного каталога
     * с характеристиками Wildberries
     *
     * @param $url
     * @param array $params
     * @param bool $getRawBody
     * @return mixed
     * @throws \Exception
     */
    public function getDirectory($url, array $params = [], $getRawBody = false): mixed
    {
        $response = $this->requestService->send('get', $url, $params, true);
        if ($getRawBody) {
            return !empty($response) ? $response->body() : '';
        }
        return !empty($response) ? $response->json() : [];
    }

    /**
     * Get info about rest of stocks
     *
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getStocks($params): array
    {
        $response = $this->requestService->send('get', $this->apiMethodUrls[__FUNCTION__], $params, true);
        return !empty($response) ? $response->json() : [];
    }

    /**
     * Get all lists
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getDirectoryList(array $params = []): array
    {
        $response = $this->requestService->send('get', $this->apiMethodUrls[__FUNCTION__], $params, true);
        return !empty($response) ? $response->json() : [];
    }

    /**
     * Get directory colors
     *
     * @return array
     * @throws \Exception
     */
    public function getDirectoryColors(): array
    {
        $response = $this->requestService->send('get', $this->apiMethodUrls[__FUNCTION__], [], true);
        return !empty($response) ? $response->json() : [];
    }
}
