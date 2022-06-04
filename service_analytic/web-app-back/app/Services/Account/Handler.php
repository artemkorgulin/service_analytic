<?php

namespace App\Services\Account;

use App\Models\Account;

abstract class Handler
{
    /**
     * @param Account $account
     * @param int $subjectId
     */
    public function __construct(protected Account $account, protected int $subjectId)
    {
    }

    abstract public function attachAccount();

    abstract public function detachAccount();

    abstract protected function clearCache();
}
