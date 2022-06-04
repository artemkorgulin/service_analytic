<?php

namespace App\DataTransferObjects\Services\OzonPerformance\Campaign;

use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class CampaignListDTO extends DataTransferObject
{

    /** @var CampaignDTO[] */
    #[CastWith(ArrayCaster::class, itemType: CampaignDTO::class)]
    public array $list;

    public int $total;
}
