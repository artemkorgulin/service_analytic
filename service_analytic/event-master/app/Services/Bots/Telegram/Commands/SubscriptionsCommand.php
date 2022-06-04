<?php

namespace App\Services\Bots\Telegram\Commands;

use App\Services\Bots\Telegram\Keyboards\SubscriptionsKeyboard;
use AnalyticPlatform\LaravelHelpers\Constants\Notifications\WayCodes;

class SubscriptionsCommand extends UserCommand
{

    protected $name = 'subscriptions';

    protected $description = 'Подписаться на уведомления';


    /**
     * @inheritDoc
     */
    public function handle()
    {
        parent::handle();

        $schemas = $this->user->getSchemas(WayCodes::TELEGRAM);

        $keyboard = SubscriptionsKeyboard::get($schemas);

        $this->replyWithMessage(['text' => 'Выберите типы уведомлений.', 'reply_markup' => $keyboard]);
    }
}
