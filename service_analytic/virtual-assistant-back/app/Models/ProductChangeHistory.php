<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductChangeHistory
 * История изменения продукта
 * @property integer $id
 * @property integer $product_id
 * @property string $name
 * @property integer $status_id
 * @property integer|null $task_id
 * @property integer $is_send
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductFeatureHistory[] $changedFeatures
 * @property-read int|null $changed_features_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductFeatureErrorHistory[] $errorFeatures
 * @property-read int|null $error_features_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductPriceChangeHistory[] $priceChanges
 * @property-read int|null $price_changes_count
 * @property-read \App\Models\OzProduct $product
 * @property-read \App\Models\OzProductStatus $status
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereIsSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductChangeHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductChangeHistory extends Model
{

    protected $table = 'oz_product_change_history';

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'status_id', 'name', 'task_id', 'is_send', 'request_data', 'response_data'
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
     * Статус товара
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OzProductStatus::class, 'status_id');
    }

    /**
     * Изменения по статусу - отдельная история
     *
     * @return HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(OzProductChangeHistoryStatus::class, 'history_id');
    }

    /**
     * Изменённые характеристики
     *
     * @return HasMany
     */
    public function changedFeatures(): HasMany
    {
        return $this->hasMany(ProductFeatureHistory::class, 'history_id');
    }

    /**
     * Ошибочные характеристики
     *
     * @return HasMany
     */
    public function errorFeatures(): HasMany
    {
        return $this->hasMany(ProductFeatureErrorHistory::class, 'history_id');
    }

    /**
     * Изменения цен
     *
     * @return HasMany
     */
    public function priceChanges(): HasMany
    {
        return $this->hasMany(ProductPriceChangeHistory::class, 'product_history_id');
    }
}
