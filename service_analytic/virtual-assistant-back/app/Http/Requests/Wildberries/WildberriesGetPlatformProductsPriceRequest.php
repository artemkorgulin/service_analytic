<?php

namespace App\Http\Requests\Wildberries;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use App\Http\Requests\Wildberries\WildberriesGetPlatformProductPriceRequest;

class WildberriesGetPlatformProductsPriceRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'query.from_file' => ['integer'],
            'query.nmid' => ['required', 'array', 'min:1', 'max:120'],
            'query.nmid.*' => [
                'numeric',
                'min:1',
                'exists:App\Models\WbProduct,nmid'
            ],
        ];
    }
}
