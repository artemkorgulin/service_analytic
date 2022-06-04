<?php

namespace App\DataTransferObjects\Services\OzonPerformance\CampaignStatistics;

use App\DataTransferObjects\Casters\DateCaster;
use Carbon\Carbon;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class CampaignStatisticsReportRequestDTO extends DataTransferObject
{

    public string $campaignId;

    /** @var string[] $campaigns */
    public array $campaigns;

    #[CastWith(DateCaster::class)]
    public ?Carbon $from;

    #[CastWith(DateCaster::class)]
    public ?Carbon $to;

    public string $groupBy;

    public array $objects;

    #[CastWith(DateCaster::class)]
    public ?Carbon $dateFrom;

    #[CastWith(DateCaster::class)]
    public ?Carbon $dateTo;

    public int $attributionDays;
}
