<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyRole;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CompanyDeleteRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'company_id' => ['required', new CompanyRole('company.delete')],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['company_id' => $this->company->id]);
    }
}
