<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyHasNoUser;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class UserCompanySearchRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'company_id' => ['nullable', 'int', new CompanyHasNoUser($this->email ?? '')],
        ];
    }
}
