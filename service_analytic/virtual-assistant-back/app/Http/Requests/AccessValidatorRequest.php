<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class AccessValidatorRequest extends BaseRequest
{
    protected static $RULES = [
        'client_id' => 'string|required',
        'api_key' => 'string|required'
    ];
}
