<?php

namespace App\Nova\Metrics;

trait MetricsRangesTrait
{
    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30      => __('30 Days'),
            60      => __('60 Days'),
            365     => __('365 Days'),
            'TODAY' => __('Today'),
            'MTD'   => __('Month To Date'),
            'QTD'   => __('Quarter To Date'),
            'YTD'   => __('Year To Date'),
            'ALL'   => __('All time'),
        ];
    }
}
