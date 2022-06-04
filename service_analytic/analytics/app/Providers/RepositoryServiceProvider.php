<?php

namespace App\Providers;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Contracts\Repositories\V1\Analysis\AnalysisBrandRepositoryInterface;
use App\Contracts\Repositories\V1\Analysis\AnalysisCategoryRepositoryInterface;
use App\Contracts\Repositories\V1\CardProductRepositoryInterface;
use App\Contracts\Repositories\V1\Categories\CategoryTreeRepositoryInterface;
use App\Contracts\Repositories\V1\CategoryVendorRepositoryInterface;
use App\Contracts\Repositories\V1\PositionRepositoryInterface;
use App\Contracts\Repositories\V1\SearchRequestsRepositoryInterface;
use App\Repositories\V1\Action\WbHistoryProductRepository;
use App\Repositories\V1\Analysis\AnalysisBrandRepository;
use App\Repositories\V1\Analysis\AnalysisCategoryRepository;
use App\Repositories\V1\CardProductRepository;
use App\Repositories\V1\Categories\CategoryTreeRepository;
use App\Repositories\V1\CategoryVendorRepository;
use App\Repositories\V1\PositionRepository;
use App\Repositories\V1\SearchRequestsRepository;
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
        $this->app->bind(CategoryTreeRepositoryInterface::class, CategoryTreeRepository::class);
        $this->app->bind(CardProductRepositoryInterface::class, CardProductRepository::class);
        $this->app->bind(CategoryVendorRepositoryInterface::class, CategoryVendorRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(SearchRequestsRepositoryInterface::class, SearchRequestsRepository::class);
        $this->app->bind(AnalysisBrandRepositoryInterface::class, AnalysisBrandRepository::class);
        $this->app->bind(AnalysisCategoryRepositoryInterface::class, AnalysisCategoryRepository::class);
        $this->app->bind(HistoryRepositoryInterface::class, WbHistoryProductRepository::class);
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
