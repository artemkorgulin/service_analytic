<?php

namespace App\Http\Requests\V1;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class DashboardRequest extends BaseRequest
{
    protected static $RULES = [
        'query.product_vendor_codes' => ['required', 'array'],
        'query.product_vendor_codes.*' => ['required', 'numeric'],
    ];

    protected static $ATTRIBUTES = [
        'query.product_vendor_codes' => 'Артикулы товаров'
    ];

    protected static $ERROR_MESSAGES = [
        'query.product_vendor_codes' => [
            'required' => 'Поле :attribute обязательное для заполнения.',
            'array' => 'Поле :attribute должно быть массивом.',
        ]
    ];
}
