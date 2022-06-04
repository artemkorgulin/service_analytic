<?php

namespace App\Http\Requests\V1;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class UserParamsRequest extends BaseRequest
{
    protected static $RULES = [
        'url' => 'required|string',
    ];
}
