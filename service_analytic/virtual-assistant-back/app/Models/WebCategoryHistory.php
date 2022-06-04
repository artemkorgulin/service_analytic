<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WebCategoryHistory
 * История веб категории
 * @property integer $id
 * @property integer $web_category_id
 * @property float $min_price минимальная цена
 * @property float $max_price максимальная цена
 * @property integer $min_reviews минимум отзывов
 * @property float $min_rating минимальный рейтинг
 * @property integer $min_photos минимум фоток
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $average_price средняя цена
 * @property integer $max_reviews максимум отзывов
 * @property-read \App\Models\WebCategory $webCategory
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereAveragePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereMaxReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereMinPhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereMinRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereMinReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategoryHistory whereWebCategoryId($value)
 * @mixin \Eloquent
 */
class WebCategoryHistory extends Model
{

    protected $table = 'oz_web_categories_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'min_price',
        'max_price',
        'average_price',
        'min_reviews',
        'min_rating',
        'min_photos',
        'web_category_id'
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

}
