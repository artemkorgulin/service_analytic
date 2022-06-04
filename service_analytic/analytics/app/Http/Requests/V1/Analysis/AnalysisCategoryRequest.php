<?php

namespace App\Http\Requests\V1\Analysis;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class AnalysisCategoryRequest extends BaseRequest
{
    protected static $RULES = [
        'subject_id' => 'required|numeric',
        'category_id' => 'required|numeric',
        'min' => 'numeric',
        'max' => 'numeric',
        'segment' => 'required|numeric',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ];
}
