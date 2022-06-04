<?php

namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Общая логика обработки запросов с фронта
 *
 * Class BaseFrontendRequest
 *
 * @package App\Http\Requests\Frontend
 */
abstract class BaseFrontendRequest extends FormRequest
{
    /**
     * @param Validator $validator
     */
    public function failedValidation(Validator $validator)
    {
        $json = [
            'success' => false,
            'data'    => [],
            'errors'  => $validator->errors()
        ];
        throw new HttpResponseException(response()->json($json, 400));
    }
}
