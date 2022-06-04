<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

use App\Rules\PhoneRule;

/**
 * Class RegistrationRequest
 * Registration validator
 *
 * @package App\Http\Requests\Api
 */
class RegistrationRequest extends BaseRequest
{
    public function rules()
    {
        $rules =  [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email:rfc,dns', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])/'],
        ];
        if (config('feature.phone-login')) {
            $rules['phone'] = ['required', new PhoneRule(), 'unique:users'];
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.unique' => __('validation.user.unique'),
            'password.regex' => __('validation.password.format')
        ];
    }
}
