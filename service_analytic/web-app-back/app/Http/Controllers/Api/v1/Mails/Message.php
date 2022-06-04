<?php

namespace App\Http\Controllers\Api\v1\Mails;

class Message
{
    public function get($user, $data = null)
    {
        return [
            'fio' => $user->name,
            'email' => $user->email,
        ];
    }
}
