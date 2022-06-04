<?php

namespace App\Models;

use App\Models\Categories\CategoryTree;
use App\Models\Categories\CategoryVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

/**
 *
 * @property int $grade
 * @property int $comments
 */
class CardProduct extends Model
{
    use HasFactory;

    public function categories(): HasManyThrough
    {
        return $this->hasManyThrough(
            CategoryTree::class,
            CategoryVendor::class,
            'vendor_code',
            'web_id',
            'vendor_code',
            'web_id'
        );
    }

    public function categoryVendors(): HasMany
    {
        return $this->hasMany(CategoryVendor::class, 'vendor_code', 'vendor_code');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function purpose(): BelongsTo
    {
        return $this->belongsTo(Purpose::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'suppliers_id', 'supplier_id');
    }

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'vendor_code', 'vendor_code');
    }

    public function stocksJsons(): HasMany
    {
        return $this->hasMany(StocksJson::class, 'vendor_code', 'vendor_code');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'vendor_code', 'vendor_code');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'vendor_code', 'vendor_code');
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'vendor_code', 'vendor_code');
    }
}
