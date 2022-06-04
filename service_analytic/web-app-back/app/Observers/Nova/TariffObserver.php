<?php

namespace App\Observers\Nova;

use App\Models\OldTariff;

class TariffObserver
{
    public function created(OldTariff $tariff)
    {
        $tariff->tariff_id = $tariff->id;
        $tariff->save();
    }
}
