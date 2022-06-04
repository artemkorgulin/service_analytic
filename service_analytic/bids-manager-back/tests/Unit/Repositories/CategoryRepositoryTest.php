<?php

namespace Tests\Unit\Repositories;

use App\Constants\Platform;
use App\Repositories\V2\Product\CategoryRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    public function testStartConditions()
    {
        $category = CategoryRepository::startConditions()->first();
        self::assertEquals(1, $category->id);
        self::assertNotEmpty($category->name);
        self::assertNotEmpty($category->external_id);
        self::assertNull($category->parent_id);
    }

    public function testGetCategory()
    {
        $category = CategoryRepository::getCategory(17027489);
        self::assertEquals(1, $category->id);
        self::assertEquals('Красота и здоровье', $category->name);
        self::assertEquals(17027489, $category->external_id);
        self::assertNull($category->parent_id);
    }

    public function testGetParentList()
    {
        $categoryList = CategoryRepository::getParentList();
        self::assertGreaterThan(25, $categoryList->count());
    }

    public function testGetProductCategory()
    {
        $accountId = DB::connection('wab')->table('accounts')->where('platform_id', Platform::SELLER_OZON_PLATFORM_ID)->first()->id;
        $category = CategoryRepository::getProductCategory(758035, $accountId);
        self::assertEquals('Бальзам, кондиционер для волос женский', $category->name);
        self::assertEquals(22825795, $category->external_id);
    }

    public function testGetTopCategory()
    {
        $category = CategoryRepository::getTopCategory(17027489);
        self::assertEquals(1, $category->id);
    }

    public function testGetVaCategoryId()
    {
        $categoryId = CategoryRepository::getVaCategoryId(17027489);
        self::assertEquals(1, $categoryId);
    }
}
