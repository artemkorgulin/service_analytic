<?php

namespace App\Contracts\Repositories\V1;

use Illuminate\Database\Eloquent\Collection;

interface SearchRequestsRepositoryInterface
{
    public function getByVendorCode($vendorCode, $request): Collection;

    public function getByVendorCodeForUser($vendorCode, $request): Collection;
}
