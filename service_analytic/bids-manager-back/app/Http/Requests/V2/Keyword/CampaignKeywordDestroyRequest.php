<?php

namespace App\Http\Requests\V2\Keyword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignKeywordDestroyRequest extends BaseRequest
{
    protected static $RULES = [
        'keyword_ids' => 'required|array',
        'keyword_ids.*' => 'integer',
        'group_id' => 'required_without:campaign_product_id|exists:groups,id',
        'campaign_product_id' => 'required_without:group_id|exists:campaign_products,id'
    ];
}
