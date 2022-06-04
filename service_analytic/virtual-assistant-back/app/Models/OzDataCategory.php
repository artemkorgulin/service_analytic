<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class OzDataCategory
 *
 * @package App\Models
 *
 * @property string $name
 */
class OzDataCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Корневые запросы
     *
     * @return HasMany
     */
    public function rootQueries()
    {
        return $this->hasMany(RootQuery::class, 'ozon_category_id', 'id');
    }
}
