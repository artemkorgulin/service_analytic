<?php

namespace App\Services\V2;

use App\Models\OzProductUser;

class OzonListProductsService
{
    /**
     * Get observed account imt_id products
     */
    public static function getAccountObservedProductExternalIds(): ?array
    {
        return OzProductUser::currentAccount()->select(['external_id'])->pluck('external_id')->toArray();
    }
}
