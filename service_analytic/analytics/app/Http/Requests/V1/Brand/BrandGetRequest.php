<?php

namespace App\Http\Requests\V1\Brand;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class BrandGetRequest extends BaseRequest
{
    protected static $RULES = [
        'id' => 'required|int',
    ];
}
