<?php

namespace App\Services;

use App\Models\User;
use AnalyticPlatform\LaravelHelpers\Constants\DateTimeConstants;
use Illuminate\Support\Facades\Cache;

/**
 * @package App\Services
 */
class CounterService
{
    public const LENDING_COUNTERS = 3500;

    public static function setCountUsersCache()
    {
        return Cache::remember('count_users', DateTimeConstants::COUNT_SECONDS_IN_HOUR, function () {
            return User::query()->count('id') + self::LENDING_COUNTERS;
        });
    }
}
