<?php

namespace App\Http\Requests\V1\Words;

use App\Http\Requests\V1\BaseFrontendRequest;

class WordsSaveFromXlsRequest extends BaseFrontendRequest
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
            'words_xls' => 'required|mimes:xls,xlsx'
        ];
    }
}
