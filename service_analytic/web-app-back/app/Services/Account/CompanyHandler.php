<?php

namespace App\Services\Account;

use App\Models\Company;
use App\Services\UserService;

class CompanyHandler extends Handler
{
    public function attachAccount()
    {
        if (!$this->account->companies()->where('companies.id', $this->subjectId)->exists()) {
            $this->account->companies()->attach($this->subjectId);
        }

        $this->clearCache();
    }

    public function detachAccount()
    {
        $this->account->companies()->detach($this->subjectId);

        $this->clearCache();
    }

    protected function clearCache()
    {
        $company = Company::where('companies.id', $this->subjectId)->first();

        foreach ($company->users as $user) {
            (new UserService($user))->forgetAllCache();
        }
    }
}
