<?php

namespace App\Exceptions\Telegram;

class UserNotFound extends TelegramException
{
    protected $message = 'Пожалуйста, зарегистрируйте бота по ссылке из личного кабинета на сайте.';
}
