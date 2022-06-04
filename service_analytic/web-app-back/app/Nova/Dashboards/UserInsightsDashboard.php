<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\AccountsCount;
use App\Nova\Metrics\UsersCount;
use Laravel\Nova\Dashboard;

class UserInsightsDashboard extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            UsersCount::make(),
            UsersCount::make()->withAccounts(),
            UsersCount::make()->attemptedFirstLogin(),
            UsersCount::make()->withPaidTariff(),
            AccountsCount::make()->ozon(),
            AccountsCount::make()->wb(),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'user-dashboard';
    }

    public static function label()
    {
        return __('User Insights');
    }
}
