<?php

namespace App\Models\Categories;

use App\Models\CardProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryVendor extends Model
{
    use HasFactory;

    protected $table = 'category_vendor';

    public function product(): BelongsTo
    {
        return $this->belongsTo(CardProduct::class, 'vendor_code', 'vendor_code');
    }
}
