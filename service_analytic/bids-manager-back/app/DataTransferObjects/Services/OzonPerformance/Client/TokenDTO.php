<?php

namespace App\DataTransferObjects\Services\OzonPerformance\Client;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class TokenDTO extends DataTransferObject
{

    public string $access_token;

    public int $expires_in;

    public string $token_type;
}
