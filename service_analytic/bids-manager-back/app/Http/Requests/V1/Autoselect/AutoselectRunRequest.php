<?php

namespace App\Http\Requests\V1\Autoselect;

use App\Http\Requests\V1\BaseFrontendRequest;

class AutoselectRunRequest extends BaseFrontendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'keyword' => 'required|string|min:3',
            'group_id' => 'nullable|exists:groups,id',
            'campaign_product_id' => 'required_without:group_id|exists:campaign_products,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'category_id' => 'nullable|exists:categories,id'
        ];
    }

    public function messages()
    {
        return [
            'keyword.required' => 'Данное поле является обязательным для заполнения',
            'keyword.min'      => 'Базовое ключевое слово не может содержать менее :min символов. Рекомендуется ввести фразу из 2-3 слов',
        ];
    }
}
