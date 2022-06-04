<?php

namespace App\Rules;

use App\Models\Company;
use App\Services\Billing\OrderService;
use Illuminate\Contracts\Validation\Rule;

class CompanyCheckService implements Rule
{
    private string $alias;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $alias)
    {
        $this->alias = $alias;
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
        $orderService = new OrderService();

        $company = Company::where('id', $value)->first();

        if ($company) {
            return $orderService->hasService($company, $this->alias);
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
        return __('validation.user_company.active_service', ['service' => $this->alias]);
    }
}
