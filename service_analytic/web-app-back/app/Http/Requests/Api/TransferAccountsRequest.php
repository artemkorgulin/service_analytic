<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyRole;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TransferAccountsRequest extends BaseRequest
{
    public function rules()
    {
        $result = [
            'account_id' => ['nullable', 'integer'],
            'company_from_id' => ['nullable', 'integer'],
            'company_to_id' => ['required', 'integer'],
        ];

        if ($this->company_from_id) {
            $result['company_from_id'][] = new CompanyRole('company.accounts.transfer');
        }

        if ($this->company_to_id) {
            $result['company_to_id'][] = new CompanyRole('company.accounts.transfer');
        }

        if ($this->account_id) {
            if ((int) $this->company_from_id === 0) {
                $result['account_id'][] = Rule::exists('user_account')
                    ->where('user_id', \Auth::user()->id)
                    ->where('account_id', $this->account_id);
            } else {
                $result['account_id'][] = Rule::exists('account_company')
                    ->where('company_id', $this->company_from_id)
                    ->where('account_id', $this->account_id);
            }
        }

        return $result;
    }

    protected function prepareForValidation()
    {
        if (is_null($this->company_from_id)) {
            $selectedCompanyId = \Auth::user()->getSelectedCompany()->id ?? 0;

            $this->merge(['company_from_id' => $selectedCompanyId]);
        }
    }
}
