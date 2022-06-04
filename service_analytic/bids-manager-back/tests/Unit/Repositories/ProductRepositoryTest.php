<?php

namespace Tests\Unit\Repositories;

use App\Repositories\V2\Product\ProductRepository;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    public function testStartConditions()
    {
        $product = ProductRepository::startConditions()->first();
        self::assertNotEmpty($product->id);
        self::assertNotEmpty($product->title);
        self::assertNotEmpty($product->external_id);
    }

    public function testGetProduct()
    {
        $product = ProductRepository::startConditions()->first();
        $checkProduct = (new ProductRepository())->getProduct($product->external_id, $product->user_id);
        self::assertEquals($checkProduct->external_id, $product->external_id);
    }

    public function testGetProducts()
    {
        $userId = ProductRepository::startConditions()->first()->user_id;
        $products = ProductRepository::startConditions()->where('user_id', $userId)->limit(2);
        $checkProducts = (new ProductRepository())->getProducts($products->pluck('external_id')->toArray(), $userId);
        self::assertEquals($checkProducts->count(), $products->get()->count());
    }

    public function testSearchProduct()
    {
        $userId = ProductRepository::startConditions()->first()->user_id;
        $productCount = (new ProductRepository())->searchProduct('Бал', $userId)->count();
        self::assertGreaterThan(20, $productCount);
    }
}
