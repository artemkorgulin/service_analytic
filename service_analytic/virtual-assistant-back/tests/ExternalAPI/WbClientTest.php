<?php

namespace Tests\ExternalAPI;

use App\Models\WbCategory;
use App\Models\WbFeature;
use App\Models\WbProduct;
use App\Services\Wildberries\Client;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class WbClientTest
 * @package Tests\Feature
 * @group wildberries
 * @group api
 */
class WbClientTest extends TestCase
{
    use WithFaker;

    /** @var Client */
    private Client $client;

    /**
     * Setup for all tests
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $clientId = env('TEST_WB_CLIENT_ID');
        $apiKey = env('TEST_WB_API_KEY');
        if (empty($clientId) || empty($apiKey)) {
            $this->markTestSkipped('empty TEST_WB_CLIENT_ID or TEST_WB_CLIENT_ID in .env.testing');
        }
        $this->client = new Client($clientId, $apiKey, 1);
    }

    /**
     * Get random product from DB with trashed
     * @return WbProduct
     */
    public function randomProduct(): WbProduct
    {
        $product = WbProduct::withTrashed()->inRandomOrder()->first();
        if (empty($product)) {
            $this->markTestSkipped('No products in DB to check method');
        }
        return $product;
    }

    /** TESTS **/

    public function testMethodsListIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->apiMethodUrls);
    }

    public function testAllMethodsExistUrls()
    {
        $result = true;
        foreach ($this->client->apiMethodUrls as $method => $url) {
            if (!method_exists(Client::class, $method) || empty($url)) {
                $result = false;
            }
        }
        $this->assertTrue($result);
    }

    public function testGetCardListIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->getCardList());
    }

    public function testGetInfoIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->getInfo());
    }

    public function testGetCardByImtIdIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->getCardByImtId($this->randomProduct()->imt_id));
    }

    public function testGetCardGetBarcodesIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->cardGetBarcodes());
    }

    public function testGetObjectListIsNotEmpty()
    {
        $category = WbCategory::select('name')->inRandomOrder()->first();
        if (empty($category)) {
            $this->markTestSkipped('No categories in DB to check method');
        }
        $this->assertNotEmpty($this->client->getObjectList([
            'pattern' => $category->name,
            'lang' => 'ru'
        ]));
    }

    public function testGetDirectoryExtIsNotEmpty()
    {
        $feature = WbFeature::select('title')->inRandomOrder()->first();
        if (empty($feature)) {
            $this->markTestSkipped('No features in DB to check method');
        }
        $this->assertNotEmpty($this->client->getDirectoryExt($feature->title));
    }

    public function testGetStocksIsNotEmpty()
    {
        $barcodes = $this->client->cardGetBarcodes();
        if (empty($barcodes->result->barcodes)) {
            $this->fail('empty barcodes list');
        }
        $barcode = $barcodes->result->barcodes[0];
        $this->assertNotEmpty($this->client->getStocks([
            'search' => $barcode,
            'skip' => 0,
            'take' => 100
        ]));
    }

    public function testGetDirectoryListIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->getDirectoryList());
    }

    public function testGetDirectoryColorsIsNotEmpty()
    {
        $this->assertNotEmpty($this->client->getDirectoryColors());
    }
}
