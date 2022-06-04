<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class OrderShowRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('orders')->where(function ($query) {
                    return $query->where('user_id', auth()->user()->id);
                }),
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['id' => $this->id]);
    }
}
