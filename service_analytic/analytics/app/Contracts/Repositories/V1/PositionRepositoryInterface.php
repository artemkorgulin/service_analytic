<?php

namespace App\Contracts\Repositories\V1;

interface PositionRepositoryInterface
{
    public function getByVendorCode($vendorCode, $request);
}
