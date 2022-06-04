<?php

namespace App\Http\Requests\Api;

use App\Rules\FinalPriceServices;
use Illuminate\Foundation\Http\FormRequest;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

use App\Models\Tariff;

class BillingCheckPriceRequest extends BaseRequest
{
    protected static $ATTRIBUTES = [
        'tariff_id' => 'Тариф',
        'duration' => 'Продолжительность бронирования',
        'services' => 'Набор услуг'
    ];

    public function rules()
    {
        return [
            'tariff_id' => 'nullable|integer',
            'duration' => 'required|integer|in:1,3,6,12',
            'services' => ['nullable', 'array', new FinalPriceServices(1)],
            'services.*.alias' => 'required|string',
        ];
    }

    public function tariff()
    {
        return Tariff::find($this->tariff_id);
    }
}
