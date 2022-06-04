<?php

namespace Tests\ExternalAPI;

use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Services\V2\OzonApi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class OzonApiTest
 * @package Tests\Feature
 * @group ozon
 * @group api
 */
class OzonApiTest extends TestCase
{
    use WithFaker;

    /** @var OzonApi */
    private OzonApi $client;

    const MESSAGES = [
        'Status200AndIsNotEmpty' => 'Status is not 200 or response is empty'
    ];

    /**
     * Setup for all tests
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $clientId = env('TEST_OZON_CLIENT_ID');
        $apiKey = env('TEST_OZON_API_KEY');
        if (empty($clientId) || empty($apiKey)) {
            $this->markTestSkipped('empty TEST_OZON_CLIENT_ID or TEST_OZON_API_KEY in .env.testing');
        }
        $this->client = new OzonApi($clientId, $apiKey);
    }

    public function assertStatus200AndIsNotEmpty(array $response = []): bool
    {
        return !empty($response) && !empty($response['statusCode']) && $response['statusCode'] === 200;
    }

    /**
     * Get random product from DB with trashed
     * @return OzProduct
     */
    public function randomProduct(): OzProduct
    {
        $product = OzProduct::withTrashed()->inRandomOrder()->first();
        if (empty($product)) {
            $this->markTestSkipped('No products in DB to check method');
        }
        return $product;
    }

    /** TESTS **/
    public function testValidateAccessDataShouldReturnTrue()
    {
        $this->assertTrue($this->client->validateAccessData());
    }

    public function testGetCategoriesTreeReturnStatus_200AndIsNotEmpty()
    {
        $this->assertTrue(
            $this->assertStatus200AndIsNotEmpty($this->client->getCategoriesTree()),
            self::MESSAGES['Status200AndIsNotEmpty']
        );
    }

    public function testGetProductInfoReturnStatus_200AndIsNotEmpty()
    {
        $this->assertTrue(
            $this->assertStatus200AndIsNotEmpty($this->client->getProductInfo($this->randomProduct()->sku_fbo)),
            self::MESSAGES['Status200AndIsNotEmpty']
        );
    }

    public function testGetProductInfoByIdReturnStatus_200AndIsNotEmpty()
    {
        $this->assertTrue(
            $this->assertStatus200AndIsNotEmpty($this->client->getProductInfoById($this->randomProduct()->external_id)),
            self::MESSAGES['Status200AndIsNotEmpty']
        );
    }

    public function testGetProductListReturnStatus_200AndIsNotEmpty()
    {
        $this->assertTrue(
            $this->assertStatus200AndIsNotEmpty($this->client->getProductList([
                'page' => 1,
                'page_size' => 10,
            ])),
            self::MESSAGES['Status200AndIsNotEmpty']
        );
    }

    public function testGetStocksReturnStatus_200AndIsNotEmpty()
    {
        $this->assertTrue(
            $this->assertStatus200AndIsNotEmpty($this->client->getStocks()),
            self::MESSAGES['Status200AndIsNotEmpty']
        );
    }

    public function testGetCategoryFeatureV3IsNotEmpty()
    {
        $category = OzCategory::select('external_id')->inRandomOrder()->first();
        if (empty($category)) {
            $this->markTestSkipped('No categories in DB to check method');
        }
        $this->assertNotEmpty($this->client->getCategoryFeatureV3([$category->external_id]));
    }

    public function testGetProductInfoListIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->getProductInfoList(['product_id' => [$this->randomProduct()->external_id]]));
    }

    public function testGetProductFeaturesIsNotEmpty()
    {
        $products = $this->client->getProductList(['page' => 1, 'page_size' => 1]);
        if (empty($products['data']['result']['items'][0]['offer_id'])) {
            $this->fail('No items in request getProductList');
        }
        $this->assertNotEmpty($this->client->getProductFeatures([
            $products['data']['result']['items'][0]['offer_id']
        ]));
    }

    public function testGetProductFeaturesByProductIdIsNotEmpty()
    {
        $products = $this->client->getProductList(['page' => 1, 'page_size' => 1]);
        if (empty($products['data']['result']['items'][0]['product_id'])) {
            $this->fail('No items in request getProductList');
        }
        $this->assertNotEmpty($this->client->getProductFeaturesByProductId([
            $products['data']['result']['items'][0]['product_id']
        ]));
    }

    public function testGetAnalyticsDataIsNotEmpty()
    {
        $this->assertNotEmpty(
            $this->client->getAnalyticsData(
                Carbon::yesterday()->format('Y-m-d'),
                $this->randomProduct()->sku_fbo
            )
        );
    }

    public function testOzonRepeatShouldReturnSameResponse()
    {
        $sku = $this->randomProduct()->sku_fbo;
        $this->assertEquals(
            $this->client->getProductInfo($sku),
            $this->client->ozonRepeat('getProductInfo', $sku)
        );
    }
}
