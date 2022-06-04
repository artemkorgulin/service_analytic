<?php

namespace App\Services;

use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;

use App\Contracts\SMSInterface;
use App\Helpers\PhoneHelper;
use App\Models\User;
use App\Repositories\UserRepository;


/**
 * Сервис для работы и управлением телефонами у человека
 */
class PhoneService
{
    /**
     * Время жизни кода
     */
    public const PHONE_TOKEN_LIFETIME = 300;

    /**
     * Через какое время можно повторно запросить код
     */
    public const PHONE_TOKEN_RESEND_TIME = 30;

    /**
     * Является ли телефон новым для данного пользователя
     */
    public function isNewPhone(User $user, $rawPhone)
    {
        // Этот телефон такой же как и не подтверждённый у человека
        if (PhoneHelper::normalizePhone($user->unverified_phone) === PhoneHelper::normalizePhone($rawPhone)) {
            return false;
        }
        // Этот телефон уже у человека подтверждён
        if (PhoneHelper::normalizePhone($user->phone) === PhoneHelper::normalizePhone($rawPhone)) {
            return false;
        }

        // Похоже это и правда новый телефон
        return true;
    }


    /**
     * Отправить код подтверждения на телефон
     */
    public function sendPhoneConfirmation(User $user)
    {
        app(SMSInterface::class)->send(
            PhoneHelper::normalizePhone($user->unverified_phone),
            __('sms.message', ['code' => $user->phone_verification_token])
        );
    }

    /**
     * Подтвердить номер телефона у человека, отвязав его у других (если есть)
     */
    public function setCurrentUserPhone(User $user): User
    {
        $phone = PhoneHelper::normalizePhone($user->unverified_phone);

        $user->phone = $phone;
        $user->phone_verified_at = Carbon::now();

        $user->phone_verification_token = null;
        $user->phone_verification_token_created_at = null;

        $user->unverified_phone = null;
        return $user;
    }

    /**
     * Открепить подтверждённый телефон от человека
     */
    public function detachPhone(User $user)
    {
        $user->phone = null;
        $user->phone_verified_at = null;
        return $user;
    }

    /**
     * Устарел ли токен для подтверждения телефона у пользователя
     */
    public function isTokenStale(User $user)
    {
        return $user->phone_verification_token_created_at->addSeconds(self::PHONE_TOKEN_LIFETIME)->isPast();
    }

    /**
     * Принадлежит ли токен этому пользователю
     */
    public function userHasPhoneToken(User $user, $token)
    {
        return ($user->phone_verification_token === $token);
    }

    /**
     * Возможно ли отправить очередной код для данного телефона
     */
    public function canSendNewToken($phone)
    {
        $user = (new UserRepository())->getByUnverifiedPhone($phone);

        // Если человек ещё не запрашивал токена подтверждения - то да, можем отправить
        if (!$user->phone_verification_token_created_at) {
            return true;
        }

        return $user
            ->phone_verification_token_created_at
            ->addSeconds(self::PHONE_TOKEN_RESEND_TIME)
            ->isPast();
    }

    /**
     * Нужно ли этому человеку подтвердить телефон
     */
    public function needPhoneConfirmation(User $user)
    {
        return $user->unverified_phone !== null;
    }

    /**
     * Нужно ли этому человеку нормализовать телефон
     */
    public function needPhoneNormalization(User $user)
    {
        return $user->unverified_phone !== PhoneHelper::normalizePhone($user->unverified_phone);
    }
}
