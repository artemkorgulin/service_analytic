<?php

namespace App\Http\Requests\Company;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class DeleteProductsForAllAccountUsersRequest extends BaseRequest
{
    protected static $RULES = [
        'account_id' => ['required', 'integer'],
    ];
}
