<?php

namespace Tests\Unit;

use Tests\Traits\UserForTest;
use Tests\TestCase;
use App\Models\OzProduct;
use App\Models\WbProduct;


class DevideDeponTest extends TestCase
{
    use UserForTest;

    private $wb_products = [];
    private $oz_products = [];

    private function getWbProducts() 
    {
        foreach (WbProduct::select('id')->whereNotNull('deleted_at') as $product) 
        {
            $this->wb_products[] = $product->id;
        }
        return $this->wb_products;
    }

    private function getOzProducts() 
    {
        foreach (OzProduct::select('id')->whereNotNull('deleted_at')  as $product) 
        {
            $this->oz_products[] = $product->id;
        }
        return $this->oz_products;
    }

    /**
     * Успешный запрос лимитов по продуктам с делением wildberries
     *
     * @return void
     */
    public function testWbEscrowLimitsDevideProduct()
    {
        foreach ($this->getWbProducts() as $id) 
        {
            $response = $this->json('GET', '/api/vp/v2/escrow/limits/'.$id."/devide", [], $this->getInnerRequestHeader());
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data' => [
                    '*' => [
                        'is_protect'
                    ]
                ],
                'errors',
                'success'
            ]);
        }
    }


    /**
     * Успешный запрос лимитов по продуктам с вычитанием wildberries
     *
     * @return void
     */
    public function testWbEscrowLimitsSubtractionProduct()
    {
        foreach ($this->getWbProducts() as $id) 
        {
            $response = $this->json('GET', '/api/vp/v2/escrow/limits/'.$id, [], $this->getInnerRequestHeader());
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data' => [
                    '*' => [
                        'imagesProductProtected',
                        'imagesProductTotal'
                    ]
                ],
                'errors',
                'success'
            ]);
        }
    }


    /**
     * Успешный запрос лимитов по продуктам с делением ozon
     *
     * @return void
     */
    public function testOzEscrowLimitsDevideProduct()
    {
        foreach ($this->getOzProducts() as $id) 
        {
            $response = $this->json('GET', '/api/vp/v2/escrow/limits/'.$id."/devide", [], $this->getInnerRequestHeader());
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data' => [
                    '*' => [
                        'is_protect'
                    ]
                ],
                'errors',
                'success'
            ]);
        }
    }


    /**
     * Успешный запрос лимитов по продуктам с вычитанием ozon
     *
     * @return void
     */
    public function testOzEscrowLimitsSubtractionProduct()
    {
        foreach ($this->getOzProducts() as $id) 
        {
            $response = $this->json('GET', '/api/vp/v2/escrow/limits/'.$id, [], $this->getInnerRequestHeader());
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data' => [
                    '*' => [
                        'imagesProductProtected',
                        'imagesProductTotal'
                    ]
                ],
                'errors',
                'success'
            ]);
        }
    }
}
