<?php

namespace App\Rules;

use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class CountRoles implements Rule
{
    /**
     * @param int $companyId
     */
    public function __construct(private int $companyId)
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
        return UserCompany::where('company_id', $this->companyId)->where('user_id', $value)->first()->roles()->where('guard_name', 'company')->count() > 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_company.last_role');
    }
}
