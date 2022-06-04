<?php

namespace App\Services\Billing;
use App\Models\User;
use App\Models\Promocode;

class PromocodeService
{
    /**
     * Добавить промокод пользователю
     */
    public function addToUser(User $user, Promocode $promocode)
    {
        if (!$user->promocodes()->where('code', $promocode->code)->exists()) {
            $user->promocodes()->attach($promocode);
        }
    }
}
