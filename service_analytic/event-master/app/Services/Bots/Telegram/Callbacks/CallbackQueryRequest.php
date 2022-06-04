<?php

namespace App\Services\Bots\Telegram\Callbacks;

use App\Services\Bots\Telegram\Traits\DTO\Parsable;
use App\Services\Bots\Telegram\Traits\DTO\Serializable;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
/**
 * @method __construct( )
 */
class CallbackQueryRequest extends DataTransferObject implements \JsonSerializable
{
    use Parsable, Serializable;

    public string $method;

    public $data;

}
