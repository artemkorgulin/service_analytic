<?php

namespace App\Http\Requests\V2\Brands;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class BrandFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'brand' => 'bail|required|string',
        'start_date' => 'nullable|date_format:"Y-m-d"',
        'end_date' => 'nullable|date_format:"Y-m-d"'
    ];
}
