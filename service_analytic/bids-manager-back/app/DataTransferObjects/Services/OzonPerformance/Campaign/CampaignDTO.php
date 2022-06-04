<?php

namespace App\DataTransferObjects\Services\OzonPerformance\Campaign;

use App\DataTransferObjects\Casters\DateCaster;
use App\DataTransferObjects\Casters\EnumCaster;
use App\Enums\OzonPerformance\Campaign\CampaignState;
use App\Enums\OzonPerformance\Campaign\CampaignType;
use Carbon\Carbon;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class CampaignDTO extends DataTransferObject
{

    public int $id;

    public string $title;

    #[CastWith(EnumCaster::class, enumClass: CampaignState::class)]
    public CampaignState $state;

    #[CastWith(EnumCaster::class, enumClass: CampaignType::class)]
    public CampaignType $advObjectType;

    #[CastWith(DateCaster::class)]
    public ?Carbon $fromDate;

    #[CastWith(DateCaster::class)]
    public ?Carbon $toDate;

    public int $dailyBudget;

    /** @var string[] $placement */
    public array $placement;

    public int $budget;

    #[CastWith(DateCaster::class)]
    public ?Carbon $createdAt;

    #[CastWith(DateCaster::class)]
    public ?Carbon $updatedAt;
}
