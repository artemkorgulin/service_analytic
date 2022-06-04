<?php

namespace App\DataTransferObjects\Casters;

use Spatie\DataTransferObject\Caster;

class EnumCaster implements Caster
{

    public function __construct(array $types, private string $enumClass)
    {
    }


    public function cast(mixed $value): mixed
    {
        return new $this->enumClass($value);
    }
}
