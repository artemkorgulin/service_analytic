<?php

namespace App\Services\Escrow\Interfaces;

use App\Http\Requests\EscrowOzonRequest;
use App\Http\Requests\EscrowWildberriesRequest;
use App\Services\Escrow\IregService;

interface EscrowServiceInterface
{
    function processOzonEscrow(EscrowOzonRequest $escrowRequest, IregService $iregService, $productId): mixed;

    function processWbEscrow(EscrowWildberriesRequest $escrowRequest, IregService $iregService, EscrowWildberriesRequest $escrowWildberriesRequest): mixed;
}
