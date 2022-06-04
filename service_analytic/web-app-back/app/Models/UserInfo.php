<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Пользователь
 * @property int id
 * @property int user_id
 * @property string type
 * @property string payment_token
 * @property string name
 * @property string full_name
 * @property string inn
 * @property string kpp
 * @property double balance
 * @property int tariff_id
 * @property DateTime captured_at
 * @property DateTime created_at
 * @property DateTime updated_at
 */

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'user_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type', 'payment_token', 'name', 'full_name', 'inn', 'kpp',
        'balance', 'tariff_id', 'captured_at',
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

}
