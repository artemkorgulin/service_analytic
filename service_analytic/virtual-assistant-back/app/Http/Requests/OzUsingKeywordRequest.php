<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzUsingKeywordRequest extends BaseRequest
{
    protected static $RULES = [
        'product_id' => ['required', 'int'],
    ];
}
