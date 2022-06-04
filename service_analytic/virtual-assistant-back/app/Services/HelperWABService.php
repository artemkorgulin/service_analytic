<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service common class for get information
 */
class HelperWABService
{
    /**
     * Get account from database project WAB
     * @param false $accountId
     * @return array
     */
    public static function getAccount($accountId = 0): array
    {
        if (!$accountId) {
            return [];
        }
        return (array)DB::connection('wab')->table('accounts')->where('id', $accountId)->first();
    }

    /**
     * Return user from database project WAB
     * @param false $userId
     * @return array
     */
    public static function getUser($userId = 0): array
    {
        if (!$userId) {
            return [];
        }
        return (array)DB::connection('wab')->table('users')->where('id', $userId)->first();
    }

}
