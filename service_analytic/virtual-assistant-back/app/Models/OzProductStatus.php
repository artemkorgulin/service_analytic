<?php

namespace App\Models;

use App\Constants\References\ProductStatusesConstants;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OzProductStatus
 * Статус проверки товара в Озон
 * @property integer $id
 * @property string $code
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductStatus whereName($value)
 * @mixin \Eloquent
 */
class OzProductStatus extends Model
{
    protected $table = 'oz_product_statuses';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name'
    ];

    /**
     * Статус модерации
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getModerationStatus()
    {
        return OzProductStatus::query()->where('code', ProductStatusesConstants::MODERATION_CODE)->first();
    }
}
