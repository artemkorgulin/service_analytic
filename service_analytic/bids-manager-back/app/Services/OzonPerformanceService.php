<?php

namespace App\Services;

use App\Constants\Platform;
use App\DataTransferObjects\AccountDTO;
use App\DataTransferObjects\Services\OzonPerformance\Campaign\CampaignListDTO;
use App\DataTransferObjects\Services\OzonPerformance\CampaignStatistics\CampaignStatisticsDTO;
use App\DataTransferObjects\Services\OzonPerformance\CampaignStatistics\CampaignStatisticsReportStateDTO;
use App\DataTransferObjects\Services\OzonPerformance\Client\TokenDTO;
use App\Helpers\DateHelper;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;
use Psr\SimpleCache\InvalidArgumentException;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @property array $errors
 */
class OzonPerformanceService extends OzonService
{
    public const CONNECTION_SLEEP_TIME = 5;
    public const MAX_CONNECT_TRIES = 3;
    public const SECONDS_BEFORE_TOKEN_EXPIRATION = 5;

    private Repository $cacheRepository;


    public function __construct()
    {
        parent::__construct();
        $this->cacheRepository = Cache::store('redis');
    }

    /**
     * @param AccountDTO|array $account
     * @param Client|null $httpClient
     * @param Repository|null $cacheRepository
     * @return OzonPerformanceService|bool
     * @throws UnknownProperties|InvalidArgumentException|JsonException
     */
    public static function connectRepeat(
        AccountDTO|array $account,
        ?Client $httpClient = null,
        ?Repository $cacheRepository = null
    ): OzonPerformanceService|bool {
        $accountDTO = is_array($account) ? new AccountDTO($account) : $account;
        $i = 0;
        do {
            $ozonPerformanceService = self::connect($accountDTO, $httpClient, $cacheRepository);

            if ($ozonPerformanceService->token === false) {
                sleep(self::CONNECTION_SLEEP_TIME);
            }
        } while ($ozonPerformanceService->token === false && ++$i <= self::MAX_CONNECT_TRIES);

        if ($ozonPerformanceService->token === false) {
            Log::error(
                sprintf(
                    'Ошибка подключения к маркетплейсу %s для аккаунта %s',
                    $accountDTO->platform_id,
                    $accountDTO->platform_client_id
                ),
                [self::getLastError()]
            );
        }

        return $ozonPerformanceService;
    }

    /**
     * self connector
     * @param AccountDTO $account
     * @param Client|null $httpClient
     * @param Repository|null $cacheRepository
     * @return OzonPerformanceService|bool
     * @throws InvalidArgumentException|JsonException
     */
    public static function connect(
        AccountDTO $account,
        ?Client $httpClient = null,
        ?Repository $cacheRepository = null
    ): OzonPerformanceService|bool {
        if (empty($account->id) || !config('ozon.enable_ozon_service')) {
            return false;
        }

        $ozonPerformanceService = new static();
        $ozonPerformanceService->setAccount($account);

        if ($httpClient) {
            $ozonPerformanceService->setHttpClient($httpClient);
        }

        if ($cacheRepository) {
            $ozonPerformanceService->setCacheRepository($cacheRepository);
        }

        if (!$ozonPerformanceService->isRequestPossible()) {
            return false;
        }

        $ozonPerformanceService->token = $ozonPerformanceService->getAuthToken();

        return $ozonPerformanceService->token ? $ozonPerformanceService : false;
    }

    /**
     * @param AccountDTO $account
     * @param int $sleepTime
     *
     * @return bool
     * @throws Exception
     */
    public static function handleError(
        AccountDTO $account,
        int $sleepTime = 1
    ): bool {
        $lastError = self::getLastError();

        if (!$lastError) {
            return false;
        }

        $errorCode = $lastError['code'];

        switch ($errorCode) {
            case 400:
                Log::error('Wrong Ozon Performance request '.$lastError['message']);

                return true;
            case 429:
                $message = $lastError['message'];

                if ($jsonStartPosition = strpos($message, '{')) {
                    $errorData = json_decode(substr($message, $jsonStartPosition), true, 512, JSON_THROW_ON_ERROR);
                } else {
                    $errorData = $message;
                }

                Log::warning(
                    sprintf(
                        'Too many Ozon Performance requests for account %d. Will wait for %s',
                        $account->id,
                        DateHelper::formatTimeInterval($sleepTime)
                    ),
                    $errorData
                );

                sleep($sleepTime);
                break;

            default:
                Log::error('Error Ozon Performance request ', Arr::only($lastError, ['code', 'message']));
                break;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isRequestPossible(): bool
    {
        if (!$this->account) {
            Log::warning('Аккаунт не задан');

            return false;
        }

        if ($this->account->current_count_request >= $this->account->max_count_request_per_day
            && !$this->isRequestFirstForToday()
        ) {
            Log::warning(sprintf(
                'На аккаунте %s превышен дневной лимит в %s запросов',
                $this->account->id,
                $this->account->max_count_request_per_day
            ));

            return false;
        }

        return true;
    }

    /**
     * Set VA current_count_request and last_request_day from accounts table
     */
    public function setAccountRequestCount(): void
    {
        if ($this->isRequestFirstForToday()) {
            $count = 0;
        } else {
            $count = $this->account->current_count_request + 1;
        }

        DatabaseService::getWabTableQuery('accounts')
            ->where('id', '=', $this->account->id)
            ->update(['last_request_day' => Carbon::now(), 'current_count_request' => $count]);
    }

    /**
     * Get Ozon auth token API
     *
     * @return TokenDTO|false
     * @throws InvalidArgumentException|JsonException
     */
    protected function getAuthToken(): TokenDTO|false
    {
        if (!config('ozon.enable_ozon_service')) {
            return false;
        }

        $cacheRepository = $this->getCacheRepository();
        $cacheKey = $this->getAuthTokenCacheKey();
        if ($cacheRepository->has($cacheKey)) {
            return $cacheRepository->get($cacheKey);
        }

        /** @var TokenDTO $result */
        $result = $this->request(
            self::METHOD_POST,
            '/client/token',
            [
                'client_id' => $this->account->platform_client_id,
                'client_secret' => $this->account->platform_api_key,
                'grant_type' => 'client_credentials'
            ],
            TokenDTO::class
        );

        if ($result) {
            $cacheRepository->put($cacheKey, $result, $this->getAuthTokenCacheDuration($result->expires_in));
        }

        return $result;
    }

    /**
     * Запрос к Озону
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     *
     * @return bool|object
     */
    protected function baseRequest(string $method, string $uri, array $params = []): object|bool
    {
        if (!$this->isRequestPossible()) {
            return false;
        }

        $result = $this->send(Platform::ADM_OZON_API_URL, $method, $uri, $params);

        if ($result !== false) {
            $this->setAccountRequestCount();
        }

        return $result;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param string|null $dtoClass
     * @return mixed
     * @throws JsonException
     */
    protected function request(string $method, string $uri, array $params = [], string|null $dtoClass = null): mixed
    {
        $result = $this->baseRequest($method, $uri, $params);
        $result = json_decode($result, !is_null($dtoClass), 512, JSON_THROW_ON_ERROR) ?: false;

        //parse result to dto if possible
        if ($result
            && $dtoClass
            && class_exists($dtoClass)
            && is_subclass_of($dtoClass, DataTransferObject::class)
        ) {
            $result = new $dtoClass($result);
        }

        return $result;
    }

    /**
     * Получить рекламные кампании клиента
     *
     * @param array $campaignIds
     * @param string $advObjectType
     * @param string $state
     *
     * @return bool|object
     * @throws JsonException
     */
    public function getClientCampaigns(
        array $campaignIds = [],
        string $advObjectType = '',
        string $state = ''
    ): CampaignListDTO|false
    {
        return $this->request(
            self::METHOD_GET,
            '/client/campaign', compact('campaignIds', 'advObjectType', 'state'),
            CampaignListDTO::class
        );
    }

    /**
     * Получить объекты рекламных кампаний клиента
     *
     * @param integer $campaignId
     *
     * @return bool|object
     * @throws JsonException
     */
    public function getClientCampaignObjects(int $campaignId): object|bool
    {
        return $this->request(self::METHOD_GET, '/client/campaign/'.$campaignId.'/objects');
    }

    /**
     * Получить товары РК клиента
     *
     * @param integer $campaignId
     *
     * @return bool|object
     * @throws JsonException
     */
    public function getCampaignProducts(int $campaignId): object|bool
    {
        return $this->request(self::METHOD_GET, '/client/campaign/'.$campaignId.'/products');
    }

    /**
     * @param array $campaigns
     * @param Carbon|CarbonImmutable $from
     * @param Carbon|CarbonImmutable $to
     * @param string $groupBy
     *
     * @return CampaignStatisticsDTO|false
     * @throws JsonException
     */
    public function requestClientStatistics(
        array $campaigns,
        Carbon|CarbonImmutable $from,
        Carbon|CarbonImmutable $to,
        string $groupBy
    ): CampaignStatisticsDTO|false {
        $fromStringDate = $from->toISOString();
        $toStringDate = $to->toISOString();

        return $this->request(
            self::METHOD_POST,
            '/client/statistics',
            compact('campaigns', 'fromStringDate', 'toStringDate', 'groupBy'),
            CampaignStatisticsDTO::class
        );
    }

    /**
     * @param array $params
     *
     * @return mixed
     * @throws JsonException
     */
    public function getClientStatisticsPhrases(array $params): mixed
    {
        return $this->request(self::METHOD_POST, '/client/statistics/phrases', $params);
    }

    /**
     * Проверить статус отчета со статистикой
     *
     * @param string $uuid
     *
     * @return bool|object
     * @throws JsonException
     */
    public function getClientStatisticsReportState(string $uuid): CampaignStatisticsReportStateDTO|false
    {
        return $this->request(
            method: self::METHOD_GET,
            uri: '/client/statistics/'.$uuid,
            dtoClass: CampaignStatisticsReportStateDTO::class
        );
    }


    /**
     *
     * @param int|null $page
     * @param int|null $pageSize
     *
     * @return mixed
     * @throws JsonException
     */
    public function getClientStatisticsReportList(int $page = null, int $pageSize = null): mixed
    {
        return $this->request(self::METHOD_GET, '/client/statistics/list', compact('page', 'pageSize'));
    }

    /**
     * Получить отчет со статистикой
     *
     * @param $uuid
     *
     * @return bool|object
     */
    public function getClientStatisticsReport($uuid): bool|object
    {
        return $this->baseRequest(self::METHOD_GET, '/client/statistics/report?UUID='.$uuid);
    }

    /**
     * Редактирование ставки ключевого слова
     *
     * @param integer $campaignId
     * @param array $params
     *
     * @return mixed
     * @throws JsonException
     */
    public function updateProductsBids(int $campaignId, array $params): mixed
    {
        return $this->request(self::METHOD_PUT, '/client/campaign/'.$campaignId.'/products', $params);
    }

    /**
     * Редактирование ставки группы
     *
     * @param integer $campaignId
     * @param integer $groupId
     * @param array $params
     *
     * @return mixed
     * @throws JsonException
     */
    public function updateGroupBids(int $campaignId, int $groupId, array $params): mixed
    {
        return $this->request(self::METHOD_PUT, '/client/campaign/'.$campaignId.'/group/'.$groupId, $params);
    }

    /**
     * Создание рекламной кампании
     *
     * @param array $params
     *
     * @return mixed
     * @throws JsonException
     */
    public function createCampaign(array $params): mixed
    {
        return $this->request(self::METHOD_POST, '/client/campaign/cpm/product', $params);
    }

    /**
     * @param int $campaignId
     *
     * @return mixed
     * @throws JsonException
     */
    public function activateCampaign(int $campaignId): mixed
    {
        return $this->request(self::METHOD_POST, '/client/campaign/'.$campaignId.'/activate');
    }

    /**
     * @param int $campaignId
     *
     * @return mixed
     * @throws JsonException
     */
    public function deactivateCampaign(int $campaignId): mixed
    {
        return $this->request(self::METHOD_POST, '/client/campaign/'.$campaignId.'/deactivate');
    }

    /**
     * @param int $campaignId
     * @param array $params
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function addProductsToCampaign(int $campaignId, array $params): mixed
    {
        return $this->request(self::METHOD_POST, '/client/campaign/'.$campaignId.'/products', $params);
    }

    /**
     * @param int $campaignId
     * @param array $params
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function updateWordsToCampaignProducts(int $campaignId, array $params): mixed
    {
        return $this->request(self::METHOD_PUT, '/client/campaign/'.$campaignId.'/products', $params);
    }

    /**
     * @param int $campaignId
     * @param array $params
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function removeProductFromCampaign(int $campaignId, array $params): mixed
    {
        return $this->request(self::METHOD_POST, '/client/campaign/'.$campaignId.'/products/delete', $params);
    }

    /**
     * @param int $campaignId
     * @param array $params
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function addGroupToCampaign(int $campaignId, array $params): mixed
    {
        return $this->request(self::METHOD_POST, '/client/campaign/'.$campaignId.'/group', $params);
    }

    /**
     * Редактирование бюджета кампании
     *
     * @param integer $campaignId
     * @param array $params
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function updateCampaign(int $campaignId, array $params): mixed
    {
        return $this->request('PATCH', '/client/campaign/'.$campaignId, $params);
    }

    /**
     * Редактирование статуса кампании
     *
     * @param integer $campaignId
     * @param string $status
     *
     * @return mixed
     * @throws JsonException
     */
    public function updateCampaignStatus(int $campaignId, string $status): mixed
    {
        //TODO такого метода нет в API Озон
        return $this->request(self::METHOD_POST, '/client/campaign/'.$campaignId.'/'.$status);
    }

    /**
     * @return string
     */
    private function getAuthTokenCacheKey(): string
    {
        return sprintf('account_%d_performance_token', $this->account->id);
    }

    /**
     * Get duration time for auth token
     * before token will be expired
     *
     * @param int $expires_in
     *
     * @return int
     */
    private function getAuthTokenCacheDuration(int $expires_in): int
    {
        return $expires_in - self::SECONDS_BEFORE_TOKEN_EXPIRATION;
    }

    /**
     * Returns true if no requests were made today
     *
     * @return bool
     */
    private function isRequestFirstForToday(): bool
    {
        return $this->account->current_count_request === 0
            || !$this->account->last_request_day->isSameDay(Carbon::now()->startOfDay());
    }


    public function setCacheRepository(Repository $cacheRepository): void
    {
        $this->cacheRepository = $cacheRepository;
    }


    public function getCacheRepository(): Repository
    {
        return $this->cacheRepository;
    }
}
