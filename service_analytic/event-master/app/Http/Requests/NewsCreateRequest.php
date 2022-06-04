<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class NewsCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'author_id' => ['required', 'int'],
            'title' => ['required', 'string'],
            'text' => ['required', 'string'],
        ];
    }
}
