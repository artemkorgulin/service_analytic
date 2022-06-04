<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class NotificationIndexRequest extends BaseRequest
{
    protected static $RULES = [
        //'user_id' => ['required', 'int'],
        'is_active' => ['nullable', 'bool'],
    ];
}
