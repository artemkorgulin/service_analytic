<?php

namespace App\Http\Requests\V1\CardProduct;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CardProductFilterRequest extends BaseRequest
{
    protected static $RULES = [
        'category_id' => 'bail|required|integer',
        'subject_id' => 'integer',
        'start_date' => 'nullable|date_format:"Y-m-d"',
        'end_date' => 'nullable|date_format:"Y-m-d"'
    ];
}
