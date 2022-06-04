<?php

namespace App\Contracts\Repositories;

use App\Http\Requests\V2\Campaign\CampaignsFilterRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface CampaignRepositoryInterface
 *
 * @package App\Contracts\Repositories
 */
interface CampaignRepositoryInterface
{
    public function getCampaignFilterOptions(): array;

    public function allByFilters(CampaignsFilterRequest $request): Builder;

    public function getTotalStatistic(array $campaignIds): array;
}
