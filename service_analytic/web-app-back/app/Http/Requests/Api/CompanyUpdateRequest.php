<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyRole;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CompanyUpdateRequest extends BaseRequest
{
    protected static $ATTRIBUTES = [
        'name' => 'Название компании',
    ];

    public function rules()
    {
        return [
            'company_id' => ['required', new CompanyRole('company.edit')],
            'name' => 'min:3',
            'inn' => ['min:10', 'max:13', Rule::unique('companies')->ignore($this->company->id)],
            'kpp' => 'min:9',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['company_id' => $this->company->id]);
    }
}
