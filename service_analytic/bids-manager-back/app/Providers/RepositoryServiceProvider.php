<?php

namespace App\Providers;

use App\Contracts\Repositories\CampaignRepositoryInterface;
use App\Repositories\V2\Campaign\CampaignRepository;
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
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
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
