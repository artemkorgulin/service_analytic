<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;

    const PRICE_DELIMETER = 100;

    protected $casts = [
        'visible' => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot('amount');
    }

    public function getHumanPriceAttribute()
    {
        return $this->price / self::PRICE_DELIMETER;
    }

    /**
     * Только видимые на сайте тарифы
     */
    public function scopeVisible($query)
    {
       $query->where('visible', true);
    }
}
