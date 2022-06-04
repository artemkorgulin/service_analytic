<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class RootQuery
 *
 * @package App\Models
 *
 * @property string  $id
 * @property string  $name
 * @property integer $ozon_category_id
 * @property boolean $is_visible
 */
class RootQuery extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'ozon_category_id'];

    /**
     * Категория Ozon
     *
     * @return BelongsTo
     */
    public function ozonCategory()
    {
        return $this->belongsTo(OzDataCategory::class);
    }

    /**
     * Список синонимов
     *
     * @return HasMany
     */
    public function oz_aliases()
    {
        return $this->hasMany(OzAlias::class);
    }

    /**
     * Поисковые запросы
     *
     * @return BelongsToMany
     */
    public function searchQueries()
    {
        return $this->belongsToMany(SearchQuery::class);
    }

    /**
     * Связи с поисковыми запросами
     *
     * @return HasMany
     */
    public function searchQueriesLinks()
    {
        return $this->hasMany(RootQuerySearchQuery::class);
    }
}
