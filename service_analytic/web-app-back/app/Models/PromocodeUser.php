<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PromocodeUser extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getIsUsedAttribute()
    {
        return $this->order_id ? true : false;
    }
    /**
     * Пометить промокод выданым человеку
     */
    public function makeUsedForOrder(Order $order)
    {
        $this->order_id = $order->id;
        $this->save();
    }
}
