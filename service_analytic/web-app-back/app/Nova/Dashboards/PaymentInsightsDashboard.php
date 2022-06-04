<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\PaymentsCountGroup;
use App\Nova\Metrics\PaymentsSum;
use App\Nova\Metrics\PaymentsSumGroup;
use Laravel\Nova\Dashboard;

class PaymentInsightsDashboard extends Dashboard
{

    public static function label()
    {
        return __('Payment Insights');
    }


    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            PaymentsCountGroup::make(),
            PaymentsSumGroup::make(),
            PaymentsSum::make(),
            PaymentsSum::make()->byBank(),
            PaymentsSum::make()->byCard(),
        ];
    }


    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'payments-dashboard';
    }
}
