<?php

namespace App\Http\Requests\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CampaignProductSetStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'campaign_product_id' => 'required|exists:campaign_products,id',
            'status_id' => 'required|exists:statuses,id'
        ];
    }
}
