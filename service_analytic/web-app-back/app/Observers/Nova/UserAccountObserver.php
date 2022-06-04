<?php

namespace App\Observers\Nova;

use App\Models\User;
use App\Models\UserAccount;
use App\Services\AccountServices;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAccountObserver
{

    /**
     * Table that's used by UserAccount model
     *
     * @var string|null
     */
    private ?string $table;


    /**
     * @return void
     */
    public function __construct()
    {
        $this->table = (new UserAccount)->getTable();
    }


    /**
     * @param  Pivot  $pivot
     *
     * @return void
     */
    public function created(Pivot $pivot): void
    {
        $this->forgetCacheIfPivotIsUserAccount($pivot);
    }


    /**
     * @param  Pivot  $pivot
     *
     * @return void
     */
    public function deleting(Pivot $pivot): void
    {
        $this->forgetCacheIfPivotIsUserAccount($pivot);
        $this->setDefaultAccountIfPivotIsUserAccount($pivot);
    }


    /**
     * Check if pivot uses user_account table
     * and forget cache for all related users
     *
     * @param  Pivot  $pivot
     *
     * @return void
     */
    private function forgetCacheIfPivotIsUserAccount(Pivot $pivot): void
    {
        if ($this->isUserAccount($pivot) && $user = User::find($pivot->user_id)) {
            (new UserService($user))->forgetAccountsCache();
        }
    }


    /**
     * Determines if provided pivot model is user account
     *
     * @param  Pivot  $pivot
     *
     * @return bool
     */
    private function isUserAccount(Pivot $pivot): bool
    {
        return $pivot->getTable() === $this->table;
    }


    /**
     * @param  Pivot  $pivot
     *
     * @return void
     */
    private function setDefaultAccountIfPivotIsUserAccount(Pivot $pivot)
    {
        if ($this->isUserAccount($pivot)) {
            AccountServices::setFirstAvailableAccountAsSelectedForUsers($pivot->user_id, true);
        }
    }
}
