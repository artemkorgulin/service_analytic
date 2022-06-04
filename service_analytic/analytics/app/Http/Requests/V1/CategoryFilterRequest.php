<?php

namespace App\Http\Requests\V1;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CategoryFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'category_id' => 'required|numeric',
        'subject_id' => 'nullable|numeric',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ];
}
