<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

/**
 * Class SettingsRequest
 * Settings validator
 *
 * @package App\Http\Requests\Api
 */
class MailRequest extends BaseRequest
{
    protected static $RULES = [
        'type' => 'required|string',
    ];
}
