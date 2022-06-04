<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TriggerRemoveFromSale
 * Триггеры снятия с продажи
 * @property integer $id
 * @property integer $product_id
 * @property integer $is_shown триггер показан
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OzProduct $product
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale query()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale whereIsShown($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerRemoveFromSale whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TriggerRemoveFromSale extends Model
{

    protected $table = 'oz_trigger_remove_from_sale';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'is_shown',
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
