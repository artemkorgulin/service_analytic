<?php

namespace Tests\Unit\Commands;

use App\Jobs\DashboardUpdateJob;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use App\Services\V2\ProductServiceUpdater;
use App\Constants\PlatformConstants;

/**
 * @property \App\Models\Product|\Illuminate\Database\Eloquent\Builder $products
 */
class V2LoadProductsTest extends TestCase
{
    /**
     * @return void
     */
    public function testHandleSuccess()
    {
        Queue::fake();

        $this->mock(ProductServiceUpdater::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateInfo');
        });

        $this->artisan('ozon:load_products')
            ->expectsOutput('Ozon product update start')
            ->expectsOutput('Ozon product update completed')
            ->assertSuccessful();

        Queue::assertPushed(function (DashboardUpdateJob $job) {
            return $job->getMarketplaceId() === PlatformConstants::OZON_PLATFORM_ID;
        }, 1);
    }
}
