<?php

namespace App\Models\Categories;

use App\Models\CardProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $lft
 * @property int $rgt
 */
class CategoryTree extends Model
{
    use HasFactory, SoftDeletes;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            CardProduct::class,
            'category_vendor',
            'web_id',
            'vendor_code',
            'web_id',
            'vendor_code'
        );
    }
}
