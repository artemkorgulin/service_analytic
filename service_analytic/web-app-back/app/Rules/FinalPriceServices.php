<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FinalPriceServices implements Rule
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
        foreach ($value as $service) {
            if ($service['alias'] === 'semantic' && $service['amount'] > 10000) {
                return false;
            }
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
        return __('validation.final_price.sku');
    }
}
