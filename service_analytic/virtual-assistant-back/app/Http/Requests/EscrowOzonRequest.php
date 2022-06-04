<?php

namespace App\Http\Requests;

use App\Services\Escrow\EscrowService;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\Validator;

class EscrowOzonRequest extends BaseRequest
{
    protected static $RULES = [
        'product_id' => 'required|numeric',
        'email' => 'required|email',
        'full_name' => 'required|string|max:255|min:3',
        'description' => 'nullable|string|min:25',
        'copyright_holder' => 'required|string|max:255|min:3'
    ];

    protected static $ATTRIBUTES = [
        'product_id' => 'номер продукта',
        'email' => 'email',
        'full_name' => 'ФИО',
        'description' => 'описание',
        'copyright_holder' => 'владелец',
    ];

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ((new EscrowService())->getRemainEscrowLimit() <= 0) {
                response()->api_fail('У вас закончилось количество депонирований')->throwResponse();
            }
        });
    }
}
