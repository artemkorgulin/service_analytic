<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductFeatureErrorHistory
 * История ошибок редактирования характеристик товара
 * @property integer $id
 * @property integer $history_id
 * @property integer $feature_id
 * @property-read \App\Models\Feature $feature
 * @property-read \App\Models\ProductChangeHistory $history
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureErrorHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureErrorHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureErrorHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureErrorHistory whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureErrorHistory whereHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeatureErrorHistory whereId($value)
 * @mixin \Eloquent
 */
class ProductFeatureErrorHistory extends Model
{

    protected $table = 'oz_product_feature_error_history';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'history_id',
        'feature_id'
    ];

    /**
     * История редактирования товара, в рамках которой данноее изменение
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
