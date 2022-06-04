<?php

namespace App\Http\Requests\Api;

use App\Rules\CompanyCheckService;
use App\Rules\CompanyHasUser;

class UserCompanyActivateRequest extends BillingCheckPriceRequest
{
    protected static $ATTRIBUTES = [
        'id' => 'id компании',
    ];

    public function rules()
    {
        $result = [
            'id' => ['required', 'integer', new CompanyHasUser(auth()->user()->id)],
        ];

        return $result;
    }
}
