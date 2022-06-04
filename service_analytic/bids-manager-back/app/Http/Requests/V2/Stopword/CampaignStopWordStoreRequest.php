<?php

namespace App\Http\Requests\V2\Stopword;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignStopWordStoreRequest extends BaseRequest
{
    protected static $RULES = [
        'stop_words' => 'required|array|min:1|max:100',
        'stop_words.*.stop_word_name' => 'required|string',
        'stop_words.*.campaign_product_id' => 'required_without:stop_words.*.group_id|exists:campaign_products,id',
        'stop_words.*.group_id' => 'required_without:stop_words.*.campaign_product_id|exists:groups,id',
    ];
}
