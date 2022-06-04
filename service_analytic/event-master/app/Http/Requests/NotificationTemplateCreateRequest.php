<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationTemplateCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'template' => ['required', 'string'],
            'lang' => ['required', 'string'],
            'subtype_id' => ['required', Rule::exists('notification_subtypes', 'id')],
        ];
    }
}
