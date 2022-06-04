<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class WbPickListRequest extends BaseRequest
{
    protected static $RULES = [
        'id' => 'nullable|int',
        'wb_product_id' => 'nullable|int',
        'name' => 'nullable|string',
    ];
}
