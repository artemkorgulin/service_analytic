<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductPriceChangeHistory
 * История изменения цены товара
 * @property integer $id
 * @property string $price цена
 * @property integer $sent_from_va отправлено из вп
 * @property integer $is_applied применилось
 * @property string|null $errors
 * @property integer $product_id
 * @property integer $product_history_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $old_price цена до скидки
 * @property-read \App\Models\ProductChangeHistory $history
 * @property-read \App\Models\OzProduct $product
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereIsApplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereOldPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereProductHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereSentFromVa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriceChangeHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductPriceChangeHistory extends Model
{
    protected $table = 'oz_product_price_change_history';

    protected $fillable = [
        'price', 'old_price', 'sent_from_va', 'is_applied', 'errors', 'product_id', 'product_history_id',
        'created_at', 'updated_at',
    ];

    protected $attributes = [
        'sent_from_va' => true,
        'is_applied' => false,
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

    /**
     * История редактирования товара, в рамках которой данноее изменение цены
     *
     * @return BelongsTo
     */
    public function history(): BelongsTo
    {
        return $this->belongsTo(ProductChangeHistory::class, 'product_history_id');
    }
}
