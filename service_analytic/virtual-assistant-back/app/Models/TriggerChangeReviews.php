<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TriggerChangeReviews
 * Триггеры изменения минимума отзывов
 * @property integer $id
 * @property integer $web_category_id
 * @property integer $min_reviews минимум отзывов было
 * @property integer $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OzProduct $product
 * @property-read \App\Models\WebCategory $webCategory
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews query()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews whereMinReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerChangeReviews whereWebCategoryId($value)
 * @mixin \Eloquent
 */
class TriggerChangeReviews extends Model
{

    protected $table = 'oz_trigger_change_min_reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'min_reviews'
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
