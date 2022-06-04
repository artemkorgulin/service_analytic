<?php

namespace App\Http\Requests\Company;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CreateWbProductsByImtIdsRequest extends BaseRequest
{
    protected static $RULES = [
        'user_id' => ['required', 'integer'],
        'account_id' => ['required', 'integer'],
        'imt_ids' => ['array'],
        'imt_ids.*' => ['integer'],
    ];
}
