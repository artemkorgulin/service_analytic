<?php

namespace App\Http\Requests\Company;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class MoveProductsRequest extends BaseRequest
{
    protected static $RULES = [
        'source_user_id' => ['required', 'integer'],
        'recipient_user_id' => ['required', 'integer'],
        'account_id' => ['required', 'integer'],
    ];
}
