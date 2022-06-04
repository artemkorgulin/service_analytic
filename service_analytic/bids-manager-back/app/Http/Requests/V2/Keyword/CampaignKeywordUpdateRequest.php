<?php

namespace App\Http\Requests\V2\Keyword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignKeywordUpdateRequest extends BaseRequest
{
    protected static $RULES = [
        'keyword_ids' => 'required|array',
        'keyword_ids.*' => 'exists:campaign_keywords,id',
        'bid' => 'required|integer|min:35|max:2000'
    ];
}
