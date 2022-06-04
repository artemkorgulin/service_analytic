<?php

namespace App\Http\Requests\V1;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class SubjectRequest extends BaseRequest
{
    protected static $RULES = [
        'product_id' => 'required|int',
    ];
}
