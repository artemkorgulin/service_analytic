<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * Код услуги корпоративного доступа
     */
    public const ALIAS_CORP = 'corp';

    public const PERCENT_SKU_DISCOUNT = 0.00005;

    protected $casts = [
        'countable' => 'boolean',
        'sellable' => 'boolean'
    ];

    public function tariffs()
    {
        return $this->belongsToMany(Service::class)->withPivot('amount');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('ordered_amount', 'total_price');
    }
    public function prices()
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function humanPricePerItemFor($n)
    {
        return $this->prices()
                    ->where('min_amount', '<=', $n)
                    ->orderBy('min_amount', 'desc')
                    ->first()
                    ->humanPricePerItem;
    }

    public function humanTotalPriceFor($n)
    {
        $fullPrice = $this->humanTotalPriceWithoutDiscounts($n);

        return $fullPrice - $fullPrice * $this->getDiscount($n);
    }

    public function humanTotalPriceWithoutDiscounts($n)
    {
        return $this->humanPricePerItemFor($n) * $n;
    }

    public function getDiscount($n)
    {
        if ($this->alias === 'semantic') {
            if ($n > 100) {
                return $n * self::PERCENT_SKU_DISCOUNT;
            }
        }

        return 0;
    }


    /**
     * Только видимые на сайте услуги
     */
    public function scopeVisible($query)
    {
        $query->where('visible', true);
    }

    /**
     * Получить максимальную цену за данную услугу
     */
    public function maxPrice()
    {
        return $this->prices()->max('price_per_item');
    }
}
