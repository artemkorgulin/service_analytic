<?php

namespace App\Observers;

use App\Services\UserService;
use App\Models\OzProduct;

class ProductObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param \App\Models\OzProduct $product
     * @return void
     */
    public function creating(OzProduct $product)
    {

    }
}
