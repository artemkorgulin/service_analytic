<?php

namespace App\Http\Requests\Api;

use App\Models\Role;
use App\Rules\CompanyCheckService;
use App\Rules\CompanyRole;
use App\Rules\UserInCompany;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class UserCompanyRoleAddRequest extends BaseRequest
{
    public function rules()
    {
        $roles = Role::where('guard_name', 'company')->where('name', '!=', 'company.owner')->pluck('name')->implode(',');

        return [
            'company_id' => ['required', 'int', new CompanyRole('company.users.add'), new CompanyCheckService('corp')],
            'user_id' => ['required', 'int', new UserInCompany($this->company_id)],
            'roles' => ['required', 'array', 'in:' . $roles],
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
