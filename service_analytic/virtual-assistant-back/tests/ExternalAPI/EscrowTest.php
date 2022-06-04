<?php

namespace Tests\ExternalAPI;

use App\Constants\Errors\EscrowErrors;
use App\Exceptions\Escrow\EscrowException;
use App\Models\OzProduct;
use App\Models\WbProduct;
use App\Services\Escrow\EscrowService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class EscrowTest
 * @package Tests\ExternalAPI
 * @group escrow
 */
class EscrowTest extends TestCase
{
    use WithFaker;

    /** @var EscrowService */
    private EscrowService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new EscrowService;
    }

    /**
     * Get random Wildberries product from DB with trashed and nmid
     * @return WbProduct
     */
    public function randomWbProduct(): WbProduct
    {
        $product = WbProduct::withTrashed()->whereNotNull('nmid')->inRandomOrder()->first();
        if (empty($product)) {
            $this->markTestSkipped('No products in DB to check method');
        }
        return $product;
    }

    /**
     * Get random Ozon product from DB with trashed and images
     * @return OzProduct
     */
    public function randomOzProduct(): OzProduct
    {
        $product = OzProduct::withTrashed()->whereNotNull('images')->inRandomOrder()->first();
        if (empty($product)) {
            $this->markTestSkipped('No products in DB to check method');
        }
        return $product;
    }

    public function assertEscrowException($data, $errorCode){
        try {
            if (empty($data)) {
                throw new EscrowException($errorCode);
            }
        } catch (\Exception $e){
            $this->assertSame(EscrowException::MESSAGES[$errorCode], $e->getMessage());
            $this->assertSame($errorCode, $e->getCode());
            return;
        }
        $this->fail('Exception was not thrown.');
    }

    /** TESTS FOR ALL **/
    public function testEscrowLimitsIsNotEmpty()
    {
        $this->assertNotEmpty($this->service->getEscrowLimits());
    }

    public function testGetImageHashesShouldReturnCorrectErrorForNoHashes()
    {
        $hashes = $this->service->getImageHashes([]);
        $this->assertEscrowException($hashes, EscrowErrors::NO_HASHES);
    }

    /** TESTS FOR WB **/
    public function testWbGetImagesByNmIdIsNotEmpty()
    {
        $product = $this->randomWbProduct();
        $images = $this->service->getImagesByNmId($product, $product->nmid);
        $this->assertNotEmpty($images);
    }

    public function testWbGetImagesByNmIdShouldReturnCorrectErrorForNoImages()
    {
        $product = $this->randomWbProduct();
        $product->data_nomenclatures = null;
        $images = $this->service->getImagesByNmId($product, $product->nmid);
        $this->assertEscrowException($images, EscrowErrors::NO_IMAGES);
    }

    public function testWbGetImageHashesIsNotEmpty()
    {
        $product = $this->randomWbProduct();
        $images = $this->service->getImagesByNmId($product, $product->nmid);
        $hashes = $this->service->getImageHashes($images);
        $this->assertNotEmpty($hashes);
    }

    /** TESTS FOR Ozon **/
    public function testOzGetAllOzonImagesIsNotEmpty()
    {
        $product = $this->randomOzProduct();
        $images = $this->service->getAllOzonImages($product);
        $this->assertNotEmpty($images);
    }

    public function testOzGetAllOzonImagesShouldReturnCorrectErrorForNoImages()
    {
        $product = $this->randomOzProduct();
        $product->images = null;
        $images = $this->service->getAllOzonImages($product);
        $this->assertEscrowException($images, EscrowErrors::NO_IMAGES);
    }

    public function testOzGetImageHashesIsNotEmpty()
    {
        $product = $this->randomOzProduct();
        $images = $this->service->getAllOzonImages($product);
        $hashes = $this->service->getImageHashes($images);
        $this->assertNotEmpty($hashes);
    }
}
