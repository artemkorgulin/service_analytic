<?php

namespace App\Exceptions\Telegram;

class SentNotFromPrivateChat extends TelegramException
{
    protected $message = 'Бот не предназначен для использования в чатах';
}
