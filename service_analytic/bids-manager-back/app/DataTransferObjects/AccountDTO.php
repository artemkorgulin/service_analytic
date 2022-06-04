<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;
use Morningtrain\DataTransferObjectCasters\Casters\DateCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

class AccountDTO extends DataTransferObject
{

    public int $id;

    public int $platform_id;

    public ?string $platform_client_id;

    public ?string $platform_api_key;

    public string $title;

    public ?string $description;

    public $params;

    #[CastWith(DateCaster::class, format: 'Y-m-d')]
    public ?Carbon $last_request_day;

    public int $current_count_request;

    public int $max_count_request_per_day;

    #[CastWith(DateCaster::class, format: 'Y-m-d H:m:i')]
    public ?Carbon $created_at;

    #[CastWith(DateCaster::class, format: 'Y-m-d H:m:i')]
    public ?Carbon $updated_at;

    #[CastWith(DateCaster::class, format: 'Y-m-d H:m:i')]
    public ?Carbon $deleted_at;
}
