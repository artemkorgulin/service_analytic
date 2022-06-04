<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

/**
 * Class SubscriptionRequest
 * Registration validator
 *
 * @package App\Http\Requests\Api
 */
class SubscriptionRequest extends BaseRequest
{
    protected static $RULES = [
        'tariff_id' => 'required|array',
        'period' => 'required|integer|in:1',
        'paymentMethod' => 'required|in:card,bank',
        'company' => 'array',
    ];
}
