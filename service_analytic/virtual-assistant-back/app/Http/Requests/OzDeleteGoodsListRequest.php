<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzDeleteGoodsListRequest extends BaseRequest
{
    protected static $RULES = [
        'id' => 'nullable|int',
        'oz_product_id' => 'nullable|int',
        'name' => 'nullable|string',
    ];
}
