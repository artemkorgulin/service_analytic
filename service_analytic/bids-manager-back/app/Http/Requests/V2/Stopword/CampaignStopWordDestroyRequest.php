<?php

namespace App\Http\Requests\V2\Stopword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignStopWordDestroyRequest extends BaseRequest
{
    protected static $RULES = [
        'stop_word_ids' => 'required|array',
        'stop_word_ids.*' => 'integer',
        'group_id' => 'required_without:campaign_product_id|exists:groups,id',
        'campaign_product_id' => 'required_without:group_id|exists:campaign_products,id'
    ];
}
