<?php

namespace Tests\Unit\Services;

use App\Constants\Platform;
use App\DataTransferObjects\AccountDTO;
use App\DataTransferObjects\Services\OzonPerformance\Client\TokenDTO;
use App\Services\DatabaseService;
use App\Services\OzonPerformanceService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OutOfBoundsException;
use Tests\TestCase;
use Tests\Traits\TestsInvisibleMethods;
use TypeError;

//todo: cover all methods
class OzonPerformanceServiceTest extends TestCase
{

    use TestsInvisibleMethods;

    public function testConnect()
    {
        $account = $this->getAccount();
        //1. ozon service is disabled
        $this->toggleOzonService(false);
        $ozonPerformanceService = OzonPerformanceService::connect($account);
        $this->assertFalse($ozonPerformanceService, 'Ozon service is disabled');

        //2. account is empty
        try {
            OzonPerformanceService::connect(null);
        } catch (TypeError $error) {
        }
        $this->assertInstanceOf(TypeError::class, $error);

        $this->toggleOzonService(true);
        $httpClient             = $this->getHttpClientMock();
        $ozonPerformanceService = OzonPerformanceService::connect($account, $httpClient);
        //3. returns instance of OzonPerformanceService
        $this->assertInstanceOf(OzonPerformanceService::class, $ozonPerformanceService, 'Returns instance of OzonPerformanceService');

        //4. sets provided account to service
        $this->assertEquals($account, $ozonPerformanceService->getAccount(), 'Provided account is set to service');

        //5. sets provided http client to service
        $this->assertEquals($httpClient, $ozonPerformanceService->getHttpClient(), 'Provided http client is set to service');

        //5. returns false if request is impossible
        $account->current_count_request = $account->max_count_request_per_day;
        $account->last_request_day      = Carbon::today();
        $ozonPerformanceService         = OzonPerformanceService::connect($account, $httpClient);
        $this->assertFalse($ozonPerformanceService, 'Request is impossible');
    }


    public function testIsRequestFirstForToday()
    {
        $ozonService = new OzonPerformanceService();
        $account     = $this->getAccount();

        $account->last_request_day      = Carbon::yesterday()->startOfDay();
        $account->current_count_request = $account->max_count_request_per_day - 1;
        $ozonService->setAccount($account);
        $this->assertTrue(
            $this->callMethod([$ozonService, 'isRequestFirstForToday']),
            'Last request was made not today and request limit is not exceeded'
        );

        $account->current_count_request = $account->max_count_request_per_day;
        $this->assertTrue(
            $this->callMethod([$ozonService, 'isRequestFirstForToday']),
            'Last request was made not today and request limit is exceeded'
        );

        $account->last_request_day      = Carbon::today();
        $account->current_count_request = $account->max_count_request_per_day - 1;
        $this->assertFalse(
            $this->callMethod([$ozonService, 'isRequestFirstForToday']),
            'Last request was made today and request limit is not exceeded'
        );

        $account->current_count_request = $account->max_count_request_per_day;
        $this->assertFalse(
            $this->callMethod([$ozonService, 'isRequestFirstForToday']),
            'Last request was made today and request limit is exceeded'
        );
    }


    public function testGetAuthToken()
    {
        $access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.WyJFTWhVVU...qTz2XXZBv41h4';
        $expiresIn    = OzonPerformanceService::SECONDS_BEFORE_TOKEN_EXPIRATION + 2;
        $tokenType    = 'TestBearer';
        $checkToken   = new TokenDTO(access_token: $access_token, expires_in: $expiresIn, token_type: $tokenType);

        $httpClient = $this->getHttpClientMock($checkToken);

        $ozonService = new OzonPerformanceService();
        $ozonService->setHttpClient($httpClient);

        $account                        = $this->getAccount();
        $account->last_request_day      = Carbon::today();
        $account->current_count_request = 0;
        $ozonService->setAccount($account);

        $tokenCacheKey = $this->callMethod([$ozonService, 'getAuthTokenCacheKey']);

        /** @var TokenDTO $token */
        $this->toggleOzonService(false);
        $token = $this->callMethod([$ozonService, 'getAuthToken']);

        //1. check if ozon service is disabled
        $this->assertFalse($token);

        //2. check if we get right token
        $this->toggleOzonService(true);
        $token = $this->callMethod([$ozonService, 'getAuthToken']);
        $this->assertIsObject($token, 'Token is object');
        $this->assertInstanceOf(TokenDTO::class, $token, 'Token is instance of TokenDTO');
        $this->assertEquals($token, $checkToken, 'Token is the same as provided');

        //3. check if token is saved to cache
        $cachedToken = Cache::get($tokenCacheKey);
        $this->assertNotEmpty($cachedToken, 'Token was saved to cache');

        //4. check if token is taken from cache if time interval is actual (no requests are made)
        $token = $this->callMethod([$ozonService, 'getAuthToken']);
        $this->assertEquals($token, $checkToken);

        //5. check if token is cached for the
        sleep($this->callMethod([$ozonService, 'getAuthTokenCacheDuration'], $expiresIn) + 1);
        try {
            $this->callMethod([$ozonService, 'getAuthToken']);
        } catch (OutOfBoundsException $e) {
        }

        $this->assertNotEmpty($e);

        //6. check if token request doesn't return status 200 or any data
        $mock = new MockHandler([
            new Response(500, [], null),
        ]);

        $httpClient = new Client(['handler' => $mock]);
        $ozonService->setHttpClient($httpClient);
        $result = $this->callMethod([$ozonService, 'getAuthToken']);
        $this->assertFalse($result);
    }


    public function testCheckRequestIsPossible()
    {
        $account                        = $this->getAccount();
        $ozonService                    = new OzonPerformanceService();
        $account->last_request_day      = Carbon::today();
        $account->current_count_request = 1;

        $ozonService->setAccount($account);
        self::assertTrue($ozonService->isRequestPossible(), 'Account exists and request count limit isn\'t exceeded');

        $account->current_count_request = $account->max_count_request_per_day;
        $ozonService->setAccount($account);
        self::assertFalse($ozonService->isRequestPossible(), 'Request count limit exceeded');

        $account->last_request_day = Carbon::yesterday()->startOfDay();
        $ozonService->setAccount($account);
        self::assertTrue($ozonService->isRequestPossible(), 'Request count limit is exceeded, but last request was made yesterday');

        $ozonService->setAccount(null);
        self::assertFalse($ozonService->isRequestPossible(), 'Account isn\'t set');
    }


    /**
     * @return void
     * @throws \Exception
     */
    public function testSetAccountRequestCount()
    {
        $account       = $this->getAccount();
        $maxRequest    = $account->max_count_request_per_day;
        $yesterdayDate = Carbon::yesterday();

        //set yesterday request limit to be exceeded
        $this->updateAccount($account->id, ['current_count_request' => $maxRequest, 'last_request_day' => $yesterdayDate]);
        $account = $this->getAccount();
        self::assertEquals($maxRequest, $account->current_count_request, 'Make sure that account\'s request limit is exceeded');
        self::assertTrue($yesterdayDate->isSameDay($account->last_request_day), 'B');

        $this->getOzonPerformanceService();
        $account = $this->getAccount();
        self::assertEquals(0, $account->current_count_request);
        self::assertTrue(Carbon::now()->isSameDay($account->last_request_day));
    }


    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['ozon.enable_ozon_service' => true]);
    }


    /**
     * @return \App\Services\OzonPerformanceService|bool
     */
    private function getOzonPerformanceService(): false|OzonPerformanceService
    {
        $account    = $this->getAccount();
        $httpClient = $this->getHttpClientMock();

        return OzonPerformanceService::connect($account, $httpClient);
    }


    /**
     */
    private function getAccount(): AccountDTO
    {
        $accountData = DatabaseService::getWabTableQuery('accounts')
            ->where('platform_id', '=', Platform::ADM_OZON_PLATFORM_ID)
            ->first();

        return new AccountDTO((array) $accountData);
    }


    /**
     * @param  mixed  $accountId
     * @param  array  $values
     *
     * @return void
     */
    private function updateAccount(mixed $accountId, array $values): void
    {
        DB::connection('wab')
            ->table('accounts')
            ->where('id', '=', $accountId)
            ->update($values);
    }


    /**
     * @param  string  $access_token
     * @param  int  $expires_in
     *
     * @return \GuzzleHttp\Client
     */
    private function getHttpClientMock(
        TokenDTO|string $access_token = 'authtoken',
        int $expires_in = 30,
        string $token_type = 'Bearer'
    ): Client {
        if (is_string($access_token)) {
            $access_token = new TokenDTO(
                access_token: $access_token,
                expires_in: $expires_in,
                token_type: $token_type
            );
        }
        $mock = new MockHandler([
            new Response(200, [], json_encode($access_token)),
        ]);

        return new Client(['handler' => HandlerStack::create($mock)]);
    }


    /**
     * @param  bool  $status
     *
     * @return void
     */
    private function toggleOzonService(bool $status): void
    {
        config(['ozon.enable_ozon_service' => $status]);
    }
}
