<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class MenuRequest
 * Menu validator
 *
 * @package App\Http\Requests\Api
 */
class MenuRequest extends BaseRequest
{
    protected static $ATTRIBUTES = [
        'marketplace' => 'marketplace'
    ];

    protected static $ERROR_MESSAGES = [
        'marketplace.in' => 'Параметр :attribute может содержать только значения: ozon, ali, wildberries, amazon'
    ];

    /**
     * Get error messages
     *
     * @return array
     */
    public function rules()
    {
        return [
            'marketplace' => [
                'required',
                Rule::in(['ozon', 'ali', 'wildberries', 'amazon']),
            ]
        ];
    }
}
