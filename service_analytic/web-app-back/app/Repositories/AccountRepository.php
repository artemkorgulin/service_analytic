<?php

namespace App\Repositories;

use App\Models\User;

class AccountRepository
{
    /**
     * @param User $user
     * @return array
     */
    public static function getAllAvailableUserAccounts(User $user): array
    {
        $result = [];

        foreach ($user->accounts as $account) {
            $accountInfo = $account->only('id', 'platform_id', 'title', 'platform_title', 'platform_client_id', 'platform_api_key');
            $accountInfo['company_id'] = 0;
            $result[] = $accountInfo;
        }

        foreach ($user->company as $company) {
            foreach ($company->accounts as $account) {
                $accountInfo = $account->only('id', 'platform_id', 'title', 'platform_title', 'platform_client_id', 'platform_api_key');
                $accountInfo['company_id'] = $company->id;
                $result[] = $accountInfo;
            }
        }

        return $result;
    }
}
