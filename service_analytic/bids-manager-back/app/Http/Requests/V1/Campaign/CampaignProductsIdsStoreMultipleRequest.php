<?php

namespace App\Http\Requests\V1\Campaign;

use App\Http\Requests\V1\BaseFrontendRequest;

class CampaignProductsIdsStoreMultipleRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'campaign_id'              => 'required',
//            'with_group'               => 'array',
//            'with_group.*.group_name'  => 'string',
//            'with_group.*.products_id'   => 'array',
//            'with_group.*.products_id.*' => 'integer',
//            'without_group'            => 'array',
//            'without_group.*'          => 'integer',
        ];
    }
}
