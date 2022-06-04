<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Account;
use App\Models\TariffActivity;
use App\Nova\Dashboards\PaymentInsightsDashboard;
use App\Nova\Dashboards\ProductInsightsDashboard;
use App\Nova\Dashboards\UserInsightsDashboard;
use App\Nova\Metrics\PaymentsSumGroup;
use App\Nova\Metrics\UsersCount;
use App\Observers\AccountObserver;
use App\Observers\Nova\UserAccountObserver;
use App\Observers\Nova\OrderObserver;
use App\Observers\TariffActivityObserver;
use AnalyticPlatform\UserStatistics\UserStatistics;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Observable;

class NovaServiceProvider extends NovaApplicationServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Observable::make(TariffActivity::class, TariffActivityObserver::class);
        Observable::make(Account::class, AccountObserver::class);
        Observable::make(Pivot::class, UserAccountObserver::class);
        Observable::make(Order::class, OrderObserver::class);
    }


    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }


    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user->hasRole('admin');
        });
    }


    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            UsersCount::make(),
            PaymentsSumGroup::make(),
        ];
    }


    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            UserInsightsDashboard::make(),
            PaymentInsightsDashboard::make(),
            ProductInsightsDashboard::make(),
        ];
    }


    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            \Vyuldashev\NovaPermission\NovaPermissionTool::make(),
            UserStatistics::make(),
        ];
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
