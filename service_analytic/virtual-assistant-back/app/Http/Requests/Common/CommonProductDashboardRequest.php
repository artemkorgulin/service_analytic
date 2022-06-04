<?php

namespace App\Http\Requests\Common;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CommonProductDashboardRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'query.dashboard_type' => [
                'string',
                'required',
                'in:'.implode(',', array_keys(config('model.dashboard.type'))),
            ]
        ];
    }
}
