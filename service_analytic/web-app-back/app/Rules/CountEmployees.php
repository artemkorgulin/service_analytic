<?php

namespace App\Rules;

use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class CountEmployees implements Rule
{
    /**
     * @param int $count
     */
    public function __construct(private int $count)
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
        return UserCompany::where('company_id', $value)->count() < $this->count;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_company.count', ['count' => $this->count]);
    }
}
