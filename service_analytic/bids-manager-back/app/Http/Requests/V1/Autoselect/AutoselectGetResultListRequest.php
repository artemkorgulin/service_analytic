<?php

namespace App\Http\Requests\V1\Autoselect;

use App\Http\Requests\V1\BaseFrontendRequest;

class AutoselectGetResultListRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'autoselect_parameter_id' => 'required|exists:autoselect_parameters,id',
            'filter'                  => 'nullable|array',
            'filter.*.column'         => 'required',
            'filter.*.operation'      => 'in:"<", ">", "<=", ">=", "=", "!="',
            'filter.*.value'          => 'required',
            'order'                   => 'nullable|array',
            'order.field'             => 'required_with:order.direction',
            'order.direction'         => 'in:"asc", "desc"',
        ];
    }
}
