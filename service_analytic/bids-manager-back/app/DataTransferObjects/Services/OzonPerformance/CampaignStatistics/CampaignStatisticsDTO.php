<?php

namespace App\DataTransferObjects\Services\OzonPerformance\CampaignStatistics;

use Spatie\DataTransferObject\DataTransferObject;

class CampaignStatisticsDTO extends DataTransferObject
{

    public string $UUID;

    public bool $vendor;
}
