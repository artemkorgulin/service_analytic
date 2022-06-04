<?php

namespace App\Services\Account;

use App\Models\User;
use App\Services\UserService;

class UserHandler extends Handler
{
    public function attachAccount()
    {
        if (!$this->account->users()->where('users.id', $this->subjectId)->exists()) {
            $this->account->users()->attach([$this->subjectId => ['is_account_admin' => 1]]);
        }

        $this->clearCache();
    }

    public function detachAccount()
    {
        $this->account->users()->detach($this->subjectId);

        $this->clearCache();
    }

    protected function clearCache()
    {
        $user = User::where('id', $this->subjectId)->first();

        (new UserService($user))->forgetAllCache();
    }
}
