<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class WbUsingKeywordRequest extends BaseRequest
{
    protected static $RULES = [
        'date' => 'nullable|date_format:"Y-m-d"',
    ];
}
