<?php

namespace App\Providers;

use App\Models\AutoselectResult;
use App\Observers\AutoselectResultObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        AutoselectResult::observe(AutoselectResultObserver::class);
    }
}
