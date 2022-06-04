<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class NotificationSchemaIndexRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //'user_id' => ['required', 'int'],
            'type_id' => ['required', Rule::exists('notification_types', 'id')],
        ];
    }
}
