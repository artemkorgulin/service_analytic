<?php

namespace Tests\Unit\Repositories;

use App\Constants\Platform;
use App\Models\Campaign;
use App\Repositories\V2\Campaign\CampaignRepository;
use App\Repositories\V2\Product\CategoryRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CampaignRepositoryTest extends TestCase
{
    public function testGetProducts()
    {
        $campaign = Campaign::query()->first();
        $products = (new CampaignRepository())->getProducts($campaign);
        self::assertEmpty($products);
    }
}
