<?php

namespace App\Http\Requests\V1\Campaign;

use App\Http\Requests\V1\BaseFrontendRequest;

class CampaignProductStoreRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'campaign_id' => 'required|exists:campaigns,id',
            'product_id' => 'required|exists:products,id',
        ];
    }
}
