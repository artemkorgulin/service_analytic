<?php

namespace App\Helpers\UserParams\DefaultParams;

interface HandlerInterface
{
    public function getParams(int $userId): array;
}
