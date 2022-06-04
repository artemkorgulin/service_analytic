<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class PaymentsCountGroup extends Partition
{

    use MetricsCacheTrait, MetricsRangesTrait;

    /**
     * Card width
     * @var string
     */
    public $width = '1/4';


    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, Order::succeeded(), 'type')->label(function ($type) {
            $label = match ($type) {
                Order::TYPE_BANK_CARD => 'by card',
                Order::TYPE_BANK => 'by bank',
                default => $type,
            };

            return __($label);
        });
    }


    public function name()
    {
        return __('Payments');
    }


    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'payments-count-group';
    }
}
