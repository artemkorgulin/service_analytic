<?php

namespace App\Rules;

use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class CompanyHasUser implements Rule
{
    private int $userId;

    /**
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
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
        if ($value) {
            return UserCompany::where('user_id', $this->userId)->where('company_id', $value)->active()->exists();
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
        return __('validation.user_company.user_not_in_company');
    }
}
