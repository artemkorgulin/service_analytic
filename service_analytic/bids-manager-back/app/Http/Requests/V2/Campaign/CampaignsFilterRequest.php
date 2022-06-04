<?php

namespace App\Http\Requests\V2\Campaign;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignsFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'search' => 'nullable|string',
        'campaign_ids.*' => 'nullable|exists:campaigns,id',
        'campaign_payment_type_id' => 'nullable|exists:campaign_payment_types,id',
        'campaign_status_ids.*' => 'nullable|exists:campaign_statuses,id',
        'campaign_type_ids.*' => 'nullable|exists:campaign_types,id',
        'campaign_page_type_id.*' => 'nullable|exists:campaign_page_types,id',
        'campaign_placement_id' => 'nullable|exists:campaign_placements,id',
        'campaign_budget_start' => 'nullable|integer|lt:campaign_budget_end',
        'campaign_budget_end' => 'nullable|integer|gt:campaign_budget_start',
        'campaign_strategy_type_id' => 'nullable|exists:strategy_types,id'
    ];
}
