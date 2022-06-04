<?php

namespace App\Http\Requests\Admin;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

/**
 * Class RoleRequest
 * Role validator
 *
 * @package App\Http\Requests\Admin
 */
class RoleRequest extends BaseRequest
{
    protected static $RULES = [
        'role' => 'required',
        'model' => 'required'
    ];
}
