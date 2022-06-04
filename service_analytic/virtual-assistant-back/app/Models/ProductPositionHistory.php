<?php

namespace App\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductPositionHistory
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer|null $position позиция
 * @property integer $is_trigger триггер
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $category
 * @property string $date дата
 * @property-read \App\Models\OzProduct $product
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereIsTrigger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductPositionHistory extends Model
{

    protected $table = 'oz_product_positions_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'position',
        'is_trigger',
        'product_id'
    ];

    /**
     * Товар
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(OzProduct::class, 'product_id');
    }

}
