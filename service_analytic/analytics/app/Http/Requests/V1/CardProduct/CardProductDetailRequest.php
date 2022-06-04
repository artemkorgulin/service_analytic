<?php

namespace App\Http\Requests\V1\CardProduct;

use App\Contracts\ParserParams;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CardProductDetailRequest extends BaseRequest
{
    protected static $RULES = [
        'start_date' => 'nullable|date_format:"Y-m-d"',
        'end_date' => 'nullable|date_format:"Y-m-d"',
        'user.id' => 'nullable|int'
    ];

    protected function passedValidation()
    {
        if (!isset($this->end_date)) {
            $this->end_date = Carbon::yesterday()->format('Y-m-d');
        }

        if (!isset($this->start_date)) {
            $this->start_date = Carbon::parse($this->end_date)->subDays(30)->format('Y-m-d');
        }

        if ($this->start_date < ParserParams::DATE_START) {
            $this->start_date = ParserParams::DATE_START;
        }
    }
}
