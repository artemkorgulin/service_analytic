<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Promocode extends Model
{
    use HasFactory;

    /**
     * 1: Скидка на оплату услуг сервиса
     * 2: Бонус при оплате услуг сервиса
     * 3: Расширение функциональности тарифа по параметрам — максимальное кол-ва SKU
     * 4: Расширение функциональности тарифа по параметрам —  доступ к модулю
     * 7: Пополнение внутреннего баланса сервиса
     */
    const TYPE_DISCOUNT = 1;
    const TYPE_BONUS_WITH_PAYMENT = 2;
    const TYPE_INCREASE_SKU = 3;
    const TYPE_MODULE_ACCESS = 4;
    const TYPE_BALANCE = 7;

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $dates = ['start_at', 'end_at'];

    /**
     * Список людей, использовавших этот промокод
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'promocode_users');
    }

    public function promocodeUsers(): HasMany
    {
        return $this->hasMany(PromocodeUser::class);
    }

    public function getTypeDescriptionAttribute()
    {
        $descs = self::typeOptions();
        if(isset($descs[$this->type])){
            return $descs[$this->type];
        }
        throw new \Exception('Unknown promocode type #'.$this->type);
    }

    static public function typeOptions()
    {
        return [
            self::TYPE_DISCOUNT => "Скидка на оплату услуг сервиса",
            self::TYPE_BONUS_WITH_PAYMENT => "Бонус при оплате услуг сервиса",
            self::TYPE_INCREASE_SKU => "Расширение функциональности тарифа по параметрам — максимальное кол-ва SKU",
            self::TYPE_MODULE_ACCESS => "Расширение функциональности тарифа по параметрам —  доступ к модулю",
            self::TYPE_BALANCE => "Пополнение внутреннего баланса сервиса",
        ];
    }

    public function getMaskedCodeAttribute()
    {
        if(strlen($this->code) > 6){
            return substr_replace($this->code, '*****', 2, -2);
        }else{
            return substr_replace($this->code, '*****', 1, -1);
        }
    }
}
