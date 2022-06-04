<?php

namespace Tests\Feature;

use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function testMe()
    {
        $http = new TestHttp();
        $http->get('/v1/me');
        $http->assertStatus(200);
        $http->assertStructure(['data' => ['user' => ['id']]]);
        $jsonResponse = $http->json();
        self::assertEquals($jsonResponse['data']['user']['id'], 15);
    }

    public function testGetProduct()
    {
        $http = new TestHttp();
        $http->get('/vp/v2/products/12426');
        $http->assertStatus(200);
    }
}
