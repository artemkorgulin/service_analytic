<?php

namespace App\Services\Bots\Telegram\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class ChatMember extends DataTransferObject
{
	public User $user;
	public string $status;
    public ?int $until_date;
}
