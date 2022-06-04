<?php

namespace App\Providers;

use App\Contracts\SMSInterface;
use App\Contracts\Api\OzonApiInterface;
use App\Contracts\Api\WildberriesApiInterface;
use App\Models\OldTariff;
use App\Observers\Nova\TariffObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SMSInterface::class, config('sms.class'));

        $this->app->bind(OzonApiInterface::class, env('OZON_API_MOCK', '\App\Services\V2\OzonApi'));

        $this->app->bind(
            WildberriesApiInterface::class,
            env('WILDBERRIES_API_MOCK', '\App\Services\V2\WildberriesApi')
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.force_https') === true) {
            \URL::forceScheme('https');
        }

        Nova::serving(function(){
            OldTariff::observe(TariffObserver::class);
        });
    }
}
