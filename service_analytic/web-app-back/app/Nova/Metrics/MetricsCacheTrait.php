<?php

namespace App\Nova\Metrics;

trait MetricsCacheTrait
{
    /** @var int */
    private static $defaultCacheTime = 5;


    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(self::$cacheTime ?? self::$defaultCacheTime);
    }
}
