<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Category
 *
 * @package App\Models
 *
 * @property int $ozon_id
 * @property int $parent_id
 * @property string $name
 *
 * @method static Category find($productCategoryId)
 */
class Category extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Родительская категория
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
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
}
