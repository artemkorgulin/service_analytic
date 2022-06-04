<?php

namespace App\Http\Requests\V1;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CategorySubjectsRequest extends BaseRequest
{
    protected static $RULES = [
        'category_id' => 'required|numeric',
    ];
}
