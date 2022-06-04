<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzUserCategoriesRequest extends BaseRequest
{
    protected static $RULES = [
        'category' => 'string',
    ];
}
