<?php

namespace App\Http\Requests\Company;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CreateByIdsProductsRequest extends BaseRequest
{
    protected static $RULES = [
        'user_id' => ['required', 'integer'],
        'account_id' => ['required', 'integer'],
        'ids' => ['array'],
        'ids.*' => ['integer'],
    ];
}
