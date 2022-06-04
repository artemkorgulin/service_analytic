<?php

namespace App\Services\Bots\Telegram\Callbacks\ToggleSubscription;

use App\Models\NotificationSchema;
use App\Services\Bots\Telegram\Callbacks\Callback as TelegramCallback;
use App\Services\Bots\Telegram\Keyboards\SubscriptionsKeyboard;
use AnalyticPlatform\LaravelHelpers\Constants\Notifications\WayCodes;
use Illuminate\Database\Eloquent\Collection;

class ToggleSubscriptionCallback extends TelegramCallback
{

    protected string $method = 'toggle_subscription';

    /**
     * @var ToggleSubscriptionData
     */
    protected $data;


    /**
     * @return void
     * @throws \App\Exceptions\Telegram\UserNotFound
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function handle()
    {
        $user = $this->getUser();

        /** @var Collection $schemas */
        $schemas = $user->getSchemas(WayCodes::TELEGRAM);

        /** @var Collection $schemas */
        $typeId = $this->data->type_id;
        $schema = $schemas->get($typeId);

        if (!$schema) {
            $schema = new NotificationSchema(['user_id' => $user->id, 'type_id' => $typeId, 'way_code' => WayCodes::TELEGRAM]);
            $schema->save();
            $schemas->put($typeId, $schema);
        } else {
            $schema->forceDelete();
            $schemas->forget($typeId);
        }

        $keyboard = SubscriptionsKeyboard::get($schemas);


        $this->updateKeyboard($keyboard);

    }
}
