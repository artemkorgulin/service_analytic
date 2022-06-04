<?php

namespace App\Rules;

use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Contracts\Validation\Rule;

class CompanyHasNoUser implements Rule
{
    private string $email;

    private string|null $company;

    /**
     * @param  string  $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
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
        $this->company = Company::find($value)?->name;

        return !UserCompany::join('users', function ($join) {
                $join->on('user_companies.user_id', '=', 'users.id')
                    ->where('users.email', '=', $this->email);
            })
            ->where('company_id', $value)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_company.user_in_company', ['company' => $this->company]);
    }
}
