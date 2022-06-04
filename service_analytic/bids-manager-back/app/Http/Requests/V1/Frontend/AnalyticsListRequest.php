<?php

namespace App\Http\Requests\V1\Frontend;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class AnalyticsListRequest extends BaseRequest
{
    protected static $RULES = [
        'from' => 'date',
        'to' => 'date',
        'campaigns' => 'array',
        'products' => 'array',
        'per_page' => 'integer',
        'page' => 'integer'
    ];
}
