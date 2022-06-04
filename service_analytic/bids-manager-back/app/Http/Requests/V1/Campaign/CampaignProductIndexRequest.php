<?php

namespace App\Http\Requests\V1\Campaign;

use App\Http\Requests\V1\BaseFrontendRequest;

class CampaignProductIndexRequest extends BaseFrontendRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'campaign_id' => 'exists:campaigns,id'
        ];
    }
}
