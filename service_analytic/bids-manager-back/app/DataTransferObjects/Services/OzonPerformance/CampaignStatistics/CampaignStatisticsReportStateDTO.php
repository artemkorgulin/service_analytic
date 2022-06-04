<?php

namespace App\DataTransferObjects\Services\OzonPerformance\CampaignStatistics;

use App\DataTransferObjects\Casters\DateCaster;
use App\DataTransferObjects\Casters\EnumCaster;
use App\Enums\OzonPerformance\Campaign\CampaignReportState;
use Carbon\Carbon;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class CampaignStatisticsReportStateDTO extends DataTransferObject
{

    public string $UUID;

    #[CastWith(EnumCaster::class, enumClass: CampaignReportState::class)]
    public CampaignReportState $state;

    #[CastWith(DateCaster::class)]
    public ?Carbon $createdAt;

    #[CastWith(DateCaster::class)]
    public ?Carbon $updatedAt;

    public CampaignStatisticsReportRequestDTO $request;

    public ?string $link;

    public string $kind;
}
