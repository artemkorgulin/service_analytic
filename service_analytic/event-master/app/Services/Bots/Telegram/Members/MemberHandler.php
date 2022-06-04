<?php

namespace App\Services\Bots\Telegram\Members;

use App\Models\User;
use App\Services\Bots\Telegram\DTO\MyChatMember;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Objects\Update;

class MemberHandler
{
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
     * @param Update $update
     *
     * @return void
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public function handle(Update $update)
    {
        $items = $update->getRawResponse();

        if (!empty($items['my_chat_member'])) {
            $this->processMemberUpdate($items['my_chat_member']);
        }
    }


    /**
     * @param array $data
     *
     * @return void
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    private function processMemberUpdate($data)
    {
        $memberUpdate = new MyChatMember($data);

        if ($memberUpdate->new_chat_member->status === 'kicked') {
            User::where('telegram_user_id', $memberUpdate->from->id)
                ->update(['telegram_user_id' => null]);
        }
    }
}
