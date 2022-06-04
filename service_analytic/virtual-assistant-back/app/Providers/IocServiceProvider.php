<?php

namespace App\Providers;

use App\Services\Analytics\Interfaces\WbAnalyticsServiceInterface;
use App\Services\Analytics\WbAnalyticsService;
use App\Services\Escrow\EscrowService;
use App\Services\Escrow\Interfaces\EscrowServiceInterface;
use App\Services\Escrow\Interfaces\IregServiceInterface;
use App\Services\Escrow\IregService;
use App\Services\Interfaces\Json\JsonServiceInterface;
use App\Services\Interfaces\Ozon\OzonParsingServiceInterface;
use App\Services\Interfaces\Wildberries\WilberriesListProductsServiceInterface;
use App\Services\Interfaces\Wildberries\WildberriesControlProductsServiceInterface;
use App\Services\Interfaces\Wildberries\WildberriesGeneralServiceInterface;
use App\Services\Interfaces\Wildberries\WildberriesShowProductServiceInterface;
use App\Services\Json\JsonService;
use App\Services\Ozon\OzonParsingService;
use App\Services\Wildberries\WilberriesListProductsService;
use App\Services\Wildberries\WildberriesControlProductsService;
use App\Services\Wildberries\WildberriesGeneralService;
use App\Services\Wildberries\WildberriesShowProductService;
use Illuminate\Support\ServiceProvider;


class IocServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            EscrowServiceInterface::class,
            EscrowService::class
        );

        $this->app->bind(
            IregServiceInterface::class,
            IregService::class
        );

        $this->app->bind(
            WbAnalyticsServiceInterface::class,
            WbAnalyticsService::class
        );

        $this->app->bind(
            WildberriesGeneralServiceInterface::class,
            WildberriesGeneralService::class
        );

        $this->app->bind(
            WilberriesListProductsServiceInterface::class,
            WilberriesListProductsService::class
        );

        $this->app->bind(
            WildberriesShowProductServiceInterface::class,
            WildberriesShowProductService::class
        );

        $this->app->bind(
            WildberriesControlProductsServiceInterface::class,
            WildberriesControlProductsService::class
        );

        $this->app->bind(
            OzonParsingServiceInterface::class,
            OzonParsingService::class
        );

        $this->app->bind(
            JsonServiceInterface::class,
            JsonService::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
