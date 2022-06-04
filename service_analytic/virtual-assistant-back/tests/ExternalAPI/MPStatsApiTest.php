<?php

namespace Tests\ExternalAPI;

use App\Models\OzProduct;
use App\Services\V2\MPStatsApi;
use Carbon\Carbon;
use DateTime;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class MPStatsApiTest
 * @package Tests\Feature
 * @group mpstats
 * @group api
 */
class MPStatsApiTest extends TestCase
{
    use WithFaker;

    /** @var MPStatsApi */
    private MPStatsApi $client;

    /** @var string - SKU for tests */
    private string $sku;

    /** @var string - None existing SKU for tests */
    private string $skuNoneExist;

    /** @var DateTime - Date start */
    private DateTime $dateFrom;

    /** @var DateTime - Date end */
    private DateTime $dateTo;

    /**
     * Setup for all tests
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        if (empty(config('env.mpstats_host_url')) || empty(config('env.mpstats_token'))) {
            $this->markTestSkipped('empty mpstats_host_url or mpstats_token in config');
        }
        $product = OzProduct::withTrashed()->inRandomOrder()->first();
        if (empty($product)) {
            $this->markTestSkipped('No products in DB to check method');
        }
        $this->client = new MPStatsApi;
        $this->sku = $product->sku_fbo;
        $this->skuNoneExist = 1;
        $this->dateFrom = Carbon::now()->subDays(30)->toDate();
        $this->dateTo = Carbon::now()->subDay()->toDate();
    }

    public function testGetProductStatsReturnStatus_200AndIsNotEmpty(): void
    {
        $response = $this->client->getProductStats($this->sku, $this->dateFrom, $this->dateTo);
        $this->assertSame($response['statusCode'], 200);
        $this->assertNotEmpty($response['data']);
    }

    public function testGetProductStatsReturnStatus_500AndCorrectErrorForSku_0(): void
    {
        $response = $this->client->getProductStats(0, $this->dateFrom, $this->dateTo);
        $this->assertSame($response['statusCode'], 500);
        $this->assertSame($response['data'], 'Не указан идентификатор sku');
    }

    public function testGetProductStatsReturnStatus_500AndCorrectErrorForNoneExistingSku(): void
    {
        $response = $this->client->getProductStats($this->skuNoneExist, $this->dateFrom, $this->dateTo);
        $this->assertSame($response['statusCode'], 500);
        $this->assertSame($response['data'], 'sku не найден');
    }
}
