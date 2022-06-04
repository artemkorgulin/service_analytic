<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class NotificationCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'users' => ['required', 'array'],
            'users.*.id' => ['required', 'int'],
            'users.*.lang' => ['nullable', 'string'],
            'users.*.email' => ['required', 'string'],
            'event_code' => ['required', 'string'],
        ];
    }
}
