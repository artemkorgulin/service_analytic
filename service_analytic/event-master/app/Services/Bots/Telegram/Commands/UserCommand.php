<?php

namespace App\Services\Bots\Telegram\Commands;

use App\Exceptions\Telegram\SentNotFromPrivateChat;
use App\Exceptions\Telegram\UserNotFound;
use App\Models\User;
use Telegram\Bot\Commands\Command;

abstract class UserCommand extends Command
{

    private \Telegram\Bot\Objects\Chat|\Illuminate\Support\Collection $chat;

    /**
     * @var \Telegram\Bot\Objects\User
     */
    protected mixed $from;

    protected ?User $user;


    public function handle()
    {
        $this->chat = $this->update->getChat();
        $message = $this->update->getMessage();
        $this->from = $message->from;

        if ($this->chat->type !== 'private') {
            throw new SentNotFromPrivateChat($this->telegram, $this->chat->id);
        }

        //exclude start command for additional checks
        if (strpos($message->text, '/start') !== 0) {
            $this->user = User::findByTelegramId($this->from->id);

            if (!$this->user) {
                throw new UserNotFound($this->telegram, $this->chat->id);
            }
        }
    }


}
