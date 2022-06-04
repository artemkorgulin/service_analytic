<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    use HasFactory;

    public const PRICE_DELIMETER = 100;

    /**
     * Услуга, к которой относится эта цена
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Получить цену в человеческом выражении
     */
    public function getHumanPricePerItemAttribute()
    {
        return $this->price_per_item / self::PRICE_DELIMETER;
    }

    /**
     * Получить скидку, относительно максимальной цены за услугу
     */
    public function discount()
    {
        $maxPrice = $this->service->maxPrice();
        if ($maxPrice) {
            return  100 - (($this->price_per_item / $maxPrice) * 100);
        }
        return 0;
    }
}
