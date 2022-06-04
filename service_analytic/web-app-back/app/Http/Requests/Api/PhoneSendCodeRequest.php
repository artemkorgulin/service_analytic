<?php

namespace App\Http\Requests\Api;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use App\Repositories\UserRepository;
use App\Services\PhoneService;

use App\Rules\PhoneRule;

/**
 * Запрос отправки кода на номер телефона
 */
class PhoneSendCodeRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', new PhoneRule(), 'unique:users']
        ];
    }

    /**
     * Дополнительные проверки логики запроса
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->checkPhoneInDatabase($validator);
            $this->waitBeforeNextSend($validator);
        });
    }

    /**
     * Проверить что данный телефон есть в базе данных
     */
    private function checkPhoneInDatabase($validator)
    {
        if (!$validator->errors()->has('phone')) {
            if (!(new UserRepository())->existsByUnverifiedPhone($this->phone)) {
                $validator->errors()->add('phone', 'Данный телефон не запрашивал подтвержения');
            }
        }
    }

    /**
     * Проверить что прошло хоть какое-то время при запросе нового кода
     */
    public function waitBeforeNextSend($validator)
    {
        if (!$validator->errors()->has('phone')) {
            if (!(new PhoneService())->canSendNewToken($this->phone)) {
                $validator->errors()->add('phone', 'Необходимо выждать время перед запросом нового кода');
            }
        }
    }
}
