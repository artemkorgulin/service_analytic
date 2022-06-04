<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyRole;
use App\Rules\UserInCompany;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class UserCompanyRoleRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'company_id' => ['required', 'int', new CompanyRole('company.users.add')],
            'user_id' => ['required', 'int', new UserInCompany($this->company_id)],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);
    }
}
