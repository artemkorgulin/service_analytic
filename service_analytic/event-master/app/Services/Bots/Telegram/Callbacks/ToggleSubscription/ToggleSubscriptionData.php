<?php

namespace App\Services\Bots\Telegram\Callbacks\ToggleSubscription;

use App\Services\Bots\Telegram\Traits\DTO\Parsable;
use Spatie\DataTransferObject\DataTransferObject;

class ToggleSubscriptionData extends DataTransferObject
{
    use Parsable;

    public int $type_id;
}
