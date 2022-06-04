<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OzWbSearchRequest extends BaseRequest
{
    protected static $RULES = [
        'products' => 'required|array',
        'category' => 'string',
    ];

    /**
     * @param Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException(
            $validator, null,
            json_encode($this->request->all()));
    }
}
