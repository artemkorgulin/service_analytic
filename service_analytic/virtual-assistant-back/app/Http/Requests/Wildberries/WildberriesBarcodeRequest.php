<?php

namespace App\Http\Requests\Wildberries;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use App\Http\Requests\Wildberries\WildberriesGetPlatformProductPriceRequest;

class WildberriesBarcodeRequest extends BaseRequest
{

    /**
     * Rules
     * @return string[]
     */
    public function rules()
    {
        return [
            'quantity' => 'required|min:1|max:10'
        ];
    }

}
