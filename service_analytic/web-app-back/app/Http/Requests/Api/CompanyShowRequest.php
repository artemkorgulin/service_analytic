<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyRole;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CompanyShowRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'company_id' => ['required', new CompanyRole('company.show')],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['company_id' => $this->company->id]);
    }
}
