<?php

namespace App\Rules;

use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class IsCompanyOwner implements Rule
{
    /**
     * @param int $userId
     */
    public function __construct(private int $userId)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $company = Company::where('inn', $value)->first();

        if ($company) {
            return (bool) UserCompany::where('company_id', $company->id)->where('user_id', $this->userId)->first()?->hasRole('company.owner');
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_company.payment_is_company_owner');
    }
}
