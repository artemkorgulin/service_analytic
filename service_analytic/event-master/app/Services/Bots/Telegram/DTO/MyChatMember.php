<?php

namespace App\Services\Bots\Telegram\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class MyChatMember extends DataTransferObject
{
	public Chat $chat;
	public User $from;
	public int $date;
	public ChatMember $old_chat_member;
	public ChatMember $new_chat_member;
}
