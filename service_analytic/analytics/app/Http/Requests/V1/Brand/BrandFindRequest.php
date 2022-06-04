<?php

namespace App\Http\Requests\V1\Brand;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class BrandFindRequest extends BaseRequest
{
    protected static $RULES = [
        'filter' => 'required|string|min:2',
    ];
}
