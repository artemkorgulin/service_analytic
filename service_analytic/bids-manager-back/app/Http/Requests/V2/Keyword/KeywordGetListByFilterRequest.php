<?php

namespace App\Http\Requests\V2\Keyword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class KeywordGetListByFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'campaign_product_id' => 'required_without:group_id|exists:campaign_products,id',
        'group_id' => 'required_without:campaign_product_id|exists:groups,id',
        'search' => 'nullable|string'
    ];
}
