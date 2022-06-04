<?php

namespace App\Contracts\Api;

interface WildberriesApiInterface
{
    /**
     * @return bool
     */
    public function validateAccessData(): bool;

    /**
     * @return bool
     */
    public function validateAccessDataForClientId(): bool;
}
