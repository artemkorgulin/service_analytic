<?php

namespace App\Services\Bots\Telegram\Keyboards;

use App\Models\NotificationType;
use App\Services\Bots\Telegram\Callbacks\CallbackQueryRequest;
use App\Services\Bots\Telegram\Callbacks\ToggleSubscription\ToggleSubscriptionData;
use Telegram\Bot\Keyboard\Keyboard;

class SubscriptionsKeyboard
{

    const CACHE_TIME = 600; //10 minutes


    public static function get($schemas): Keyboard
    {
        $types = \Cache::remember('notification_types', self::CACHE_TIME, function() {
            return NotificationType::all();
        });

        $keyboard = Keyboard::make()->inline();

        foreach ($types as $type) {
            $hasType  = $schemas->has($type->id);

            $keyboard->row(Keyboard::inlineButton([
                'text'          => ($hasType ? '✅ ' : '❌ ').' '.$type->name,
                'callback_data' => new CallbackQueryRequest(method: 'toggle_subscription', data: new ToggleSubscriptionData(type_id: $type->id))
            ]));
        }

        return $keyboard;
    }
}
