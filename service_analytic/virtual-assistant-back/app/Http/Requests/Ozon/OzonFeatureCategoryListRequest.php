<?php

namespace App\Http\Requests\Ozon;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzonFeatureCategoryListRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'query.category_id' => ['integer', 'required', 'exists:App\Models\OzCategory,id'],
        ];
    }
}
