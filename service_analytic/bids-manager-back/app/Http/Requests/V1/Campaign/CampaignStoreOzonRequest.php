<?php

namespace App\Http\Requests\V1\Campaign;

use App\Http\Requests\V1\BaseFrontendRequest;

class CampaignStoreOzonRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'            => 'string|max:50',
            'placement_id'    => 'exists:campaign_placements,id',
            'payment_type_id' => 'exists:campaign_payment_types,id',
            'budget'          => 'nullable|integer',
            'start_date'      => 'nullable|date|after_or_equal:today',
            'finish_date'     => 'nullable|date|after:start_date'
        ];
    }
}
