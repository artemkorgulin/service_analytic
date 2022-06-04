<?php

namespace App\Http\Requests\Api;

use App\Rules\UserHasAccaunt;

class UserAccountActivateRequest extends BillingCheckPriceRequest
{
    protected static $ATTRIBUTES = [
        'id' => 'id аккаунта',
    ];

    public function rules()
    {
        return [
            'id' => ['required', 'integer', new UserHasAccaunt()],
        ];
    }
}
