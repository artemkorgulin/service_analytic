<?php

namespace App\Http\Requests\V2\Brands;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class BrandPriceAnalysisFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'brand' => 'bail|required|string',
        'segment' => 'required|numeric|min:1|max:25',
        'start_date' => 'nullable|date_format:"Y-m-d"',
        'end_date' => 'nullable|date_format:"Y-m-d"',
        'min' => 'numeric|lt:max',
        'max' => 'numeric|gt:min'
    ];
}
