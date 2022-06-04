<?php

namespace App\Http\Requests\V2\Categories;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CategoryStatisticFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'category' => 'bail|required|string',
        'start_date' => 'nullable|date_format:"Y-m-d"',
        'end_date' => 'nullable|date_format:"Y-m-d"'
    ];
}
