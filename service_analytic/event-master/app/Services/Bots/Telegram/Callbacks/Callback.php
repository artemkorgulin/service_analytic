<?php

namespace App\Services\Bots\Telegram\Callbacks;

use App\Exceptions\Telegram\UserNotFound;
use App\Models\User;
use App\Services\Bots\Telegram\CallbackInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Update;

abstract class Callback implements CallbackInterface
{

    /**
     * Callback method name
     * @var string
     */
    protected string $method;

    protected Api $telegram;

    protected CallbackQuery $callbackQuery;

    protected $data;

    protected Update $update;


    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }


    /**
     * @param  string  $method
     *
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }


    /**
     * @param  Api  $telegram
     * @param $data
     * @param  Update  $update
     *
     * @return mixed
     */
    public function make(Api $telegram, $data, Update $update)
    {
        $this->telegram      = $telegram;
        $this->data          = $data;
        $this->update        = $update;
        $this->callbackQuery = $update->callbackQuery;

        return $this->handle();
    }


    abstract public function handle();


    /**
     * @param  Keyboard  $keyboard
     *
     * @return void
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function updateKeyboard(Keyboard $keyboard): void
    {
        $this->telegram->editMessageReplyMarkup([
            'chat_id'      => $this->update->getChat()->id,
            'message_id'   => $this->update->getMessage()->messageId,
            'reply_markup' => $keyboard
        ]);
    }


    /**
     * @return User
     */
    protected function getUser(int|string $userId = null)
    {
        return User::where('telegram_user_id', $userId ?? $this->update->getChat()->id)->first()
            ?? throw new UserNotFound($this->telegram, $this->update->getChat()->id);
    }
}
