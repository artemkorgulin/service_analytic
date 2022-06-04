<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

use App\Rules\PhoneRule;

class LoginRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'email' => ['nullable', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', new PhoneRule()]
        ];
    }
}
