<?php

namespace App\Services\Bots\Telegram\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class User extends DataTransferObject
{
	public int $id;
	public bool $is_bot;
	public string $first_name;
    public ?string $last_name;
	public string $username;
    public ?string $language_code;
}
