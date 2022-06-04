<?php

namespace App\Rules;

use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class UserHasAccaunt implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return auth()->user()->accounts()->where('accounts.id', $value)->exists() ||
            auth()->user()->company()->whereHas('accounts', function ($query) use ($value) {
                $query->where('accounts.id', '=', $value);
            })->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_accaunt.user_has_accaunt');
    }
}
