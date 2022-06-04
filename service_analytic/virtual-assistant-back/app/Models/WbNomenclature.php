<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbNomenclature extends Model
{
    use HasFactory;

    public $fillable = ['account_id', 'user_id', 'nm_id', 'price', 'discount', 'promocode', 'quantity'];


    protected $casts = [
        'price' => 'float',
        'promocode' => 'int',
        'discount' => 'float',
    ];

    protected $hidden = ['pivot'];


    /**
     * Return card
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsToMany(WbCard::class, 'wb_card_wb_nomenclatures');
    }


    /**
     * Get products from current user
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUser($query)
    {
        return $query->where('user_id', '=', UserService::getUserId());
    }

    /**
     * Get products from current account
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentAccount($query)
    {
        return $query->where('account_id', '=', UserService::getCurrentAccountWildberriesId());
    }
}
