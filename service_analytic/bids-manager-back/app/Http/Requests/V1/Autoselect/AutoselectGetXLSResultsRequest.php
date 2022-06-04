<?php

namespace App\Http\Requests\V1\Autoselect;

use App\Http\Requests\V1\BaseFrontendRequest;

class AutoselectGetXLSResultsRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'autoselect_parameter_id' => 'required|exists:autoselect_parameters,id'
        ];
    }
}
