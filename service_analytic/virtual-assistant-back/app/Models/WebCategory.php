<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class WebCategory
 * Веб категория
 * @property integer $id
 * @property string $name название конечной категории на сайте
 * @property string $full_name полное название конечной категории на сайте
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property integer $external_id
 * @property-read mixed $last_history
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebCategoryHistory[] $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OzProduct[] $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WebCategory extends Model
{

    protected $table = 'oz_web_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'full_name',
        'external_id'
    ];


    /**
     * История веб-категории
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(WebCategoryHistory::class, 'web_category_id');
    }

    /**
     * Товары веб-категории
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(OzProduct::class, 'web_category_id');
    }

    /**
     * Получение последней записи из таблицы с историей веб категорией
     * @return mixed
     */
    public function getLastHistoryAttribute()
    {
        return $this->history->last();
    }
}
