<?php

namespace App\Casts;

class Percent implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return round($value * 10000) / 100;
    }


    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value / 100;
    }
}
