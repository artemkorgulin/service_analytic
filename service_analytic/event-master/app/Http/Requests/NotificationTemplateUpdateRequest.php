<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationTemplateUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'template' => ['nullable', 'string'],
            'lang' => ['nullable', 'string'],
            'subtype_id' => ['nullable', Rule::exists('notification_subtypes', 'id')],
        ];
    }
}
