<?php

namespace App\Http\Requests\V2\Campaign;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignStatisticRequest extends BaseRequest
{
    protected static $RULES = [
        'campaign_ids.*' => 'nullable|exists:campaigns,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after:start_date'
    ];
}
