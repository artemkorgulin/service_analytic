<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\UserService;

class AccountObserver
{

    /**
     * Handle the Account "saving" event.
     *
     * @param  \App\Models\Account  $account
     *
     * @return void
     */
    public function saving(Account $account): void
    {
        UserService::forgetAccountsCacheForUsers($account->users);
    }


    /**
     * Handle the Account "updated" event.
     *
     * @param  \App\Models\Account  $account
     *
     * @return void
     */
    public function updated(Account $account): void
    {
        UserService::forgetAccountsCacheForUsers($account->users);
    }


    /**
     * Handle the Account "restored" event.
     *
     * @param  \App\Models\Account  $account
     *
     * @return void
     */
    public function restored(Account $account): void
    {
        UserService::forgetAccountsCacheForUsers($account->users);
    }
}
