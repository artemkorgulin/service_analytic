<?php

namespace App\Services\Bots\Telegram\Commands;

use App\Exceptions\Telegram\UserNotFound;
use App\Models\User;

class StartCommand extends UserCommand
{

    protected $name = 'start';

    protected $description = 'start command';

    protected $pattern = '{verification_token}';


    /**
     * @inheritDoc
     */
    public function handle()
    {
        parent::handle();

        $arguments = $this->getArguments();

        if (empty($arguments['verification_token'])
            || !($user = User::where('verification_token', $arguments['verification_token'])->first())) {
            throw new UserNotFound($this->telegram, $this->from->id);
        }

        User::where('id', $user->id)->update(['telegram_user_id' => $this->from->id, 'verification_token' => null]);
        $this->replyWithMessage(['text' => sprintf('Здравствуйте, %s', $user->name)]);
    }
}
