<?php

namespace App\Services;

use App\Enums\OzonPerformance\Campaign\CampaignState;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;

class CampaignService
{

    /**
     * @param  array|null  $campaignIds
     * @param  mixed  $account
     * @param  int  $campaignTypeId
     *
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    public static function getCampaignsForAccount(?array $campaignIds, mixed $account, int $campaignTypeId): array|Collection
    {
        $query = Campaign::query()
            ->select('ozon_id as id')
            ->selectRaw('(CASE WHEN ozon_data_date IS NULL THEN DATE_SUB(DATE(created_at), INTERVAL 1 DAY) ELSE ozon_data_date END) as last_data_date')
            ->whereNotNull('ozon_id')
            ->where([
                ['account_id', $account->id],
                ['type_id', $campaignTypeId]
            ])
            ->whereIn('state', [
                CampaignState::CAMPAIGN_STATE_RUNNING(),
                CampaignState::CAMPAIGN_STATE_STOPPED(),
                CampaignState::CAMPAIGN_STATE_FINISHED()
            ])
            ->orderBy('ozon_data_date', 'asc')
            ->orderBy('campaigns.id', 'desc');

        if (!empty($campaignIds)) {
            $query->whereIn('id', $campaignIds);
        }

        return $query->get();
    }
}
