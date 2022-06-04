<?php

namespace App\Http\Requests\V1\CardProduct;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CardProductRatingRequest extends BaseRequest
{
    protected static $RULES = [
        'vendor_code' => 'required|array',
    ];
}
