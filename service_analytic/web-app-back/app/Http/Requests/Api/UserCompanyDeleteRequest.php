<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyCheckService;
use App\Rules\CompanyRole;
use App\Rules\CountEmployees;
use App\Rules\NotCompanyOwner;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class UserCompanyDeleteRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', new CompanyCheckService('corp'), new CompanyRole('company.users.add')],
            'user_id' => ['required', 'integer', 'exists:users,id', new NotCompanyOwner($this->company_id)],
        ];
    }
}
