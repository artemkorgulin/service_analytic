<?php

namespace App\Exceptions\Telegram;

use Telegram\Bot\Api;

abstract class TelegramException extends \Exception
{

    protected $code = 200;

    private Api $telegramBotApi;

    private string $chatId;


    public function __construct(Api $telegramBotApi, string $chatId, ?\Throwable $previous = null)
    {
        parent::__construct($this->message, $this->code, $previous);
        $this->telegramBotApi = $telegramBotApi;
        $this->chatId         = $chatId;
    }


    public function sendMessage()
    {
        $this->telegramBotApi->sendMessage(['text' => $this->message, 'chat_id' => $this->chatId]);
    }
}
