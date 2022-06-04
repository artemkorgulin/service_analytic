<?php

namespace App\DataTransferObjects\Casters;

use Carbon\Carbon;
use Spatie\DataTransferObject\Caster;

class DateCaster implements Caster
{

    /**
     * @param  mixed  $value
     *
     * @return Carbon|mixed Carbon instance or original value
     */
    public function cast(mixed $value): mixed
    {
        return Carbon::make($value);
    }
}
