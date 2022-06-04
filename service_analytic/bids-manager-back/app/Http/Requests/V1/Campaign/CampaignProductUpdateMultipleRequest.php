<?php

namespace App\Http\Requests\V1\Campaign;

use App\Http\Requests\V1\BaseFrontendRequest;

class CampaignProductUpdateMultipleRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'moved_products' => 'array',
            'moved_products.*' => 'integer',
            'with_group' => 'array',
            'with_group.*.group.id' => 'nullable|integer',
            'with_group.*.products' => 'array',
            'with_group.*.products.*.id' => 'nullable|integer',
            'with_group.*.products.*.sku' => 'required_without:with_group.*.products.*.id|integer',
            'without_group' => 'array',
            'without_group.products.*.id' => 'nullable|integer',
            'without_group.products.*.sku' => 'required_without:without_group.products.*.id|integer',
        ];
    }
}
