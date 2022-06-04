<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CompanyStoreRequest extends BaseRequest
{
    protected static $ATTRIBUTES = [
        'name' => 'Название компании'
    ];

    protected static $RULES = [
        'name' => 'min:3',
        'inn' => 'min:10|max:13|unique:companies',
        'kpp' => 'min:9',
    ];
}
