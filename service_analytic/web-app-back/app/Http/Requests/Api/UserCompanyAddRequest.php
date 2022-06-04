<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyCheckService;
use App\Rules\CompanyRole;
use App\Rules\CountEmployees;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class UserCompanyAddRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', new CompanyCheckService('corp'), new CompanyRole('company.users.add'), new CountEmployees(10)],
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}
