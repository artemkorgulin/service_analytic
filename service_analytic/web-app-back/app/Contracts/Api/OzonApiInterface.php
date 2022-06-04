<?php

namespace App\Contracts\Api;

interface OzonApiInterface
{
    /**
     * @return bool
     */
    public function validateAccessData(): bool;
}
