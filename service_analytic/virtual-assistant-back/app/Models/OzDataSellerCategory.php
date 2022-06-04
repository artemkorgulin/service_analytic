<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OzDataSellerCategory
 *
 * @package App\Models
 *
 * @property int     $ozon_id
 * @property int     $parent_id
 * @property string  $name
 * @property int     $ozon_category_id
 */
class OzDataSellerCategory extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Родительская категория
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(OzDataSellerCategory::class, 'parent_id', 'id');
    }

    /**
     * Категория верхнего уровня
     *
     * @return BelongsTo
     */
    public function topCategory()
    {
        $parentCategory = $this;
        while( $parentCategory->parent_id ) {
            $parentCategory = $parentCategory->parent()->first();
        }
        return $parentCategory;
    }

    /**
     * Категория из публичной части Озон
     *
     * @return BelongsTo
     */
    public function ozonCategory()
    {
        return $this->belongsTo(OzDataCategory::class, 'ozon_category_id', 'id');
    }
}
