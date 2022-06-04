<?php

namespace App\Helpers;


use Carbon\CarbonInterval;

class DateHelper
{
    /**
     * @param int $seconds
     *
     * @return string
     * @throws \Exception
     */
    public static function formatTimeInterval(int $seconds): string
    {
        return CarbonInterval::seconds($seconds)->locale('en')->cascade()->forHumans();
    }
}