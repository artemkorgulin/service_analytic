<?php

namespace App\Rules;

use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class UserInCompany implements Rule
{
    private int $company_id;

    /**
     * @param int $company_id
     */
    public function __construct(int $company_id)
    {
        $this->company_id = $company_id;
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
        return UserCompany::where('user_id', $value)->where('company_id', $this->company_id)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_company.user_not_in_company');
    }
}
