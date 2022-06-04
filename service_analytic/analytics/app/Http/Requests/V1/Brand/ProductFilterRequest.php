<?php

namespace App\Http\Requests\V1\Brand;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class ProductFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'brand_id' => 'required|numeric',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ];
}
