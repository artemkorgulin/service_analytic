<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class NotificationSchemaCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user.id' => ['required', 'int'],
            'type_id' => ['required', Rule::exists('notification_types', 'id')],
            'way_code' => 'required|in:email,sms,telegram,whatsapp',
            'ip' => ['nullable', 'string'],
            'deleted' => ['nullable', 'boolean'],
        ];
    }
}
