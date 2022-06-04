<?php

namespace App\Http\Requests\V2\Campaign;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class GroupStoreRequest extends BaseRequest
{
    protected static $RULES = [
        'name' => 'required|string',
        'products' => 'array'
    ];
}
