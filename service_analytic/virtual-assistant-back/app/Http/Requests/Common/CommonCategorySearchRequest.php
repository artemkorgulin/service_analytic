<?php

namespace App\Http\Requests\Common;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CommonCategorySearchRequest extends BaseRequest
{
    protected static $RULES = [
        'query.search' => ['string', 'min:1']
    ];
}
