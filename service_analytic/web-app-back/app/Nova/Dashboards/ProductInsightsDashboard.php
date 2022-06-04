<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\ProductsCount;
use Laravel\Nova\Dashboard;

class ProductInsightsDashboard extends Dashboard
{

    public static function label()
    {
        return __('Product Insights');
    }


    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            ProductsCount::make()->ozon(),
            ProductsCount::make()->wb(),
            ProductsCount::make()->ozonTemporary(),
            ProductsCount::make()->wbTemporary(),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'product-insights-dashboard';
    }
}
