<?php

namespace Tests\Feature;

use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Tests\TestCase;

class CategorySubjectsTest extends TestCase
{
    /**
     * Тест метода получения предметных категорий для категории.
     *
     * @return void
     */
    public function testGetCategorySubjects()
    {
        $http = new TestHttp();
        $http->get('/an/v1/wb/get/category-subjects', ['category_id' => 362]);

        $http->assertStatus(200);
        $http->assertStructure(
            [
                'success',
                'data' => [
                    'web_id',
                    'subjects' => [],
                ]
            ]
        );
    }
}
