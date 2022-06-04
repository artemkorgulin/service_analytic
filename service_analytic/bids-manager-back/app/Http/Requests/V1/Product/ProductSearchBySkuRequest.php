<?php

namespace App\Http\Requests\V1\Product;

use App\Http\Requests\V1\BaseFrontendRequest;

class ProductSearchBySkuRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'products_sku' => 'array',
            'products_sku.*' => 'integer'
        ];
    }
}
