<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ServiceShowRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('services')->where(function ($query) {
                    return $query->where('visible', true);
                }),
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['id' => $this->id]);
    }
}
