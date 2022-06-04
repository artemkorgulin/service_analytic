<?php

namespace App\Http\Requests\Ozon;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzonGetProductListRequest extends BaseRequest
{
    /**
     * @TODO Need more validation fields logic
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'query' => ['array', 'required'],
            'query.order' => ['array', 'nullable'],
            'query.filter' => ['array', 'nullable'],
            'query.filter.category_id' => ['integer', 'exists:App\Models\OzCategory,id'],
            'query.with_features' => ['boolean', 'nullable'],
            'query.search' => ['nullable'],
            'query.with_category' => ['boolean', 'nullable'],
            'query.filter_operator' => ['array', 'nullable'],
            'query.where_between' => ['array', 'nullable'],
            'query.select' => ['array', 'nullable'],
            'query.select.*' => ['string', 'required'],
        ];
    }

}
