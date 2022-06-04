<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

/**
 * Class SubscriptionRequest
 * Registration validator
 *
 * @package App\Http\Requests\Api
 */
class PDFRequest extends BaseRequest
{
    protected static $RULES = [
        'tariff_id' => 'required|array',
        'period' => 'required|integer',
        'sku' => 'required|integer',
        'company' => 'required',
    ];
}
