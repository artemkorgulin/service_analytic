<?php

namespace App\Services\Bots\Telegram;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

interface CallbackInterface
{

    public function getMethod(): string;

    public function make(Api $telegram, $data, Update $update);
}
