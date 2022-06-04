<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductFeatureHistory
 * История изменения характеристик товара
 * @property integer $id
 * @property integer $history_id
 * @property integer $feature_id
 * @property string $value значение
 * @property integer $is_send отправлено
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Feature $feature
 * @property-read \App\Models\ProductChangeHistory $history
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereIsSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureHistory whereValue($value)
 * @mixin \Eloquent
 */
class ProductFeatureHistory extends Model
{

    protected $table = 'oz_product_feature_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'history_id',
        'feature_id',
        'value',
        'is_send'
    ];

    /**
     * История редактирования товара, в рамках которой данное изменение
     *
     * @return BelongsTo
     */
    public function history(): BelongsTo
    {
        return $this->belongsTo(ProductChangeHistory::class, 'history_id');
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

}
