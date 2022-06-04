<?php

namespace App\Http\Requests\V1;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CategoryAnalysisRequest extends BaseRequest
{
    protected static $RULES = [
        'brands' => 'required|array',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ];
}
