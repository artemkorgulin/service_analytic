<?php

namespace App\Http\Requests\Ozon;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzonProductShowRequest extends BaseRequest
{
    protected static $RULES = [
        'product' => 'required|integer|exists:platform_semantics,product_id'
    ];

    public function validationData(): array
    {
        return array_merge($this->all(), $this->route()->parameters());
    }
}
