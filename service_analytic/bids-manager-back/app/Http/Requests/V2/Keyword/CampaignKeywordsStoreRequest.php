<?php

namespace App\Http\Requests\V2\Keyword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignKeywordsStoreRequest extends BaseRequest
{
    protected static $RULES = [
        'keywords' => 'array|max:100',
        'keywords.*.keyword_id' => 'required_without:keywords.*.keyword_name|exists:keywords,id',
        'keywords.*.keyword_name' => 'required_without:keywords.*.keyword_id|string',
        'keywords.*.campaign_product_id' => 'required_without:keywords.*.group_id|exists:campaign_products,id',
        'keywords.*.group_id' => 'required_without:keywords.*.campaign_product_id|exists:groups,id',
        'keywords.*.bid' => 'integer|min:35|max:2000',
    ];
}
