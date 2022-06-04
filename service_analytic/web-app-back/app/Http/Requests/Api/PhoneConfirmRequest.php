<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use App\Services\PhoneService;
use App\Repositories\UserRepository;

use App\Rules\PhoneRule;

/**
 * Запрос проверки кода
 */
class PhoneConfirmRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', new PhoneRule(), 'unique:users'],
            'token' => ['required', 'string'],
        ];
    }

    /**
     * Дополнительные проверки логики запроса
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->checkIsTokenForCurrentUser($validator);
            $this->checkTokenLifetime($validator);
        });
    }

    /**
     * Проверить время жизни токена
     */
    private function checkTokenLifetime($validator)
    {
        if (!$validator->errors()->has('token')) {
            if ((new PhoneService())->isTokenStale((new UserRepository())->getByUnverifiedPhone($this->phone))) {
                $validator->errors()->add('token', 'Токен устарел');
            }
        }
    }

    /**
     * Проверить принадлежность токена
     */
    private function checkIsTokenForCurrentUser($validator)
    {
        if (!$validator->errors()->has('token')) {
            if (!(new PhoneService())->userHasPhoneToken((new UserRepository())->getByUnverifiedPhone($this->phone), $this->token)) {
                $validator->errors()->add('token', 'Токен недействителен');
            }
        }
    }
}
