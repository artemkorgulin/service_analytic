<?php

namespace App\Repositories;

use App\Models\User;

class UserCompanyRepository
{

    /**
     * @param User|int $user
     * @param int $company_id
     * @return array
     */
    public function getPermissions(User|int $user,int $company_id): array
    {
        if (is_int($user)) {
            $user = User::find($user);
        }

        return $user->userCompanies()->where('company_id', $company_id)->first()?->getAllPermissions()->pluck('name')->toArray() ?? [];
    }
}
