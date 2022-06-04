<?php

namespace App\Services\Bots\Telegram\Callbacks;

use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Objects\Update;

class CallbackQueriesHandler
{

    private Api $telegram;

    private BotsManager $manager;


    /**
     * @param  Api  $telegram
     * @param  BotsManager  $manager
     */
    public function __construct(Api $telegram, BotsManager $manager)
    {
        $this->telegram = $telegram;
        $this->manager  = $manager;
    }


    /**
     * @param  Update  $update
     *
     * @return void
     */
    public function handle(Update $update)
    {
        if ($update->callbackQuery) {
            $this->getCallbackQueryBus()->handler($update);
        }
    }


    /**
     * @return CallbackQueryBus
     */
    private function getCallbackQueryBus(): CallbackQueryBus
    {
        return CallbackQueryBus::Instance($this->telegram, $this->manager);
    }


}
