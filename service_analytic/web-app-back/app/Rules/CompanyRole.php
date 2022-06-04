<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CompanyRole implements Rule
{
    private string $permission;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $permission)
    {
        $this->permission = $permission;
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
        $userCompany = auth()->user()->userCompany($value)->first();
        if ($userCompany) {
            return $userCompany->hasPermissionTo($this->permission);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_company.role');
    }
}
