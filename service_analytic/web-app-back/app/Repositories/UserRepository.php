<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

use App\Models\User;
use App\Models\Account;
use App\Models\UserAccount;

class UserRepository
{

    /**
     * Get ids of users
     * who will be left with no selected account
     *
     * @param  \App\Models\Account  $account
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getUserIdsWhoSelectedAccount(Account $account): Collection
    {
        return UserAccount::query()
            ->where([
                'is_selected' => true,
                'account_id'  => $account->id,
            ])
            ->pluck('user_id');
    }


    /**
     * Получить пользователя, по неподтверждённому номеру телефона
     */
    public function getByUnverifiedPhone($phone): User
    {
        return User::where('unverified_phone', $phone)->first();
    }

    /**
     * Проверить существование в базе данных человека с таким неподтверждённым телефоном
     */
    public function existsByUnverifiedPhone($phone): bool
    {
        return User::where('unverified_phone', $phone)->exists();
    }
}
