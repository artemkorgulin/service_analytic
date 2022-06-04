<?php

namespace Tests\Unit\Commands;

use App\Jobs\DashboardUpdateJob;
use App\Services\InnerService;
use App\Services\Wildberries\Client;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use App\Constants\PlatformConstants;

/**
 * @property \App\Models\Product|\Illuminate\Database\Eloquent\Builder $products
 */
class WbLoadProductsTest extends TestCase
{

    /**
     * @return void
     */
    public function testHandleSuccess()
    {
        Queue::fake();

        $this->mock(InnerService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAccount');
        });

        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('getInfo');
        });

        $this->artisan('wb:load_products')
            ->expectsOutput('Получаю информацию о продуктах (карточках товаров) Wildberries')
            ->assertExitCode(0);

        Queue::assertPushed(function (DashboardUpdateJob $job) {
            return $job->getMarketplaceId() === PlatformConstants::WILDBERRIES_PLATFORM_ID;
        }, 1);
    }
}
