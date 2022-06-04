<?php

namespace Tests\Unit\App\Services\Escrow;

use App\Services\Escrow\EscrowMethods;
use App\Services\UserService;
use Tests\Mock\Classes\App\Services\UserServiceMock;
use Tests\TestCase;

class EscrowMethodsTest extends TestCase
{
    /**
     * @param int $escrowLimit
     * @param int $sku
     * @param int $result
     * @param bool $loadMock
     * @return void
     *
     * @dataProvider providerGetEscrowLastPercent
     * TODO переделать на атрибуты когда обновим phpunit до 10 версии
     * #[DataProvider(className: self::class, method: providerGetEscrowLastPercent)]
     */
    public function testGetEscrowLastPercent(int $escrowLimit, int $sku, int $result, bool $loadMock = false): void
    {
        if ($loadMock == true) {
            class_alias(UserServiceMock::class, UserService::class);
        }

        $escrowMethods = new EscrowMethods();
        UserServiceMock::$testEscrowSku = $escrowLimit;
        UserServiceMock::$testSku = $sku;
        $this->assertSame($escrowMethods->getEscrowLastPercent(), $result);
    }

    public function providerGetEscrowLastPercent(): array
    {
        return [
            [0, 5, 0, true],
            [10, 5, 200],
            [-10, 5, 0],
            [10, 10, 100],
            [10, 0, 0],
            [10, -10, 0]
        ];
    }
}
