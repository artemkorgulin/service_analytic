<?php

namespace Tests\Mock\Classes\App\Services;

class UserServiceMock
{
    public static int $testEscrowSku = 0;
    public static int $testSku = 0;

    public static function getEscrowSku(): int
    {
        return self::$testEscrowSku;
    }

    public static function getAccounts(): ?array
    {
        return null;
    }

    public static function getSku(): int
    {
        return self::$testSku;
    }
}
