<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TriggerChangePhotos
 * Триггеры изменения минимума фото
 * @property integer $id
 * @property integer $web_category_id
 * @property integer $min_photos минимум фото было
 * @property integer $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OzProduct $product
 * @property-read \App\Models\WebCategory $webCategory
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos query()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos whereMinPhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangePhotos whereWebCategoryId($value)
 * @mixin \Eloquent
 */
class TriggerChangePhotos extends Model
{

    protected $table = 'oz_trigger_change_min_photos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'min_photos'
    ];

    /**
     * Web категория
     *
     * @return BelongsTo
     */
    public function webCategory(): BelongsTo
    {
        return $this->belongsTo(WebCategory::class, 'web_category_id');
    }

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
