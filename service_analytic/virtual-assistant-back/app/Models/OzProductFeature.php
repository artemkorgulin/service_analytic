<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OzProductFeature
 * Связь между товаром и хар-кой
 * @property integer $id
 * @property integer $product_id
 * @property integer $feature_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property integer|null $option_id
 * @property-read \App\Models\Feature $feature
 * @property-read \App\Models\Option|null $option
 * @property-read \App\Models\OzProduct $product
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzProductFeature whereValue($value)
 * @mixin \Eloquent
 */
class OzProductFeature extends Model
{

    protected $table = 'oz_products_features';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'product_id', 'feature_id', 'option_id', 'value', 'created_at', 'updated_at',
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
     * Характеристика
     *
     * @return BelongsTo
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    /**
     * Значение характеристики
     *
     * @return BelongsTo
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'option_id');
    }

}
