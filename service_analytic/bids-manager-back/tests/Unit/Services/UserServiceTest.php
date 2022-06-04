<?php

namespace Tests\Unit\Services;

use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function testGetWabAccount()
    {
        $accountId = DB::connection('wab')->table('accounts')->first()->id;
        $result = UserService::getWabAccount($accountId);
        self::assertEquals($result->id, $accountId);
    }

    public function testGetAllAdmAccounts()
    {
        $accounts = UserService::getAdmAccounts();
        self::assertGreaterThanOrEqual(1, $accounts->count());
    }
}
