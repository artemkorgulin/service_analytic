<?php

namespace App\Providers;

use App\Repositories\Interfaces\Wildberries\WildberriesProductRepositoryInterface;
use App\Repositories\Wildberries\WildberriesProductRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            WildberriesProductRepositoryInterface::class,
            WildberriesProductRepository::class
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
