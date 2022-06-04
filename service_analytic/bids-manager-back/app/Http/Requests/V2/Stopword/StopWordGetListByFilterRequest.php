<?php

namespace App\Http\Requests\V2\Stopword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class StopWordGetListByFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'campaign_product_id' => 'required_without:group_id|exists:campaign_products,id',
        'group_id' => 'required_without:campaign_product_id|exists:groups,id',
        'search' => 'nullable|string'
    ];
}
