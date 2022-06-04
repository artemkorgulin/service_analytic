<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    public $fillable = [
        'product_id', 'title', 'sku', 'sku_manufacturer', 'barcode', 'guid', 'small_description', 'description',
        'meta_title', 'meta_keywords', 'meta_description', 'retail_price', 'retail_price_currency', 'sale_price', 'sale_price_currency', 'whosale_price', 'whosale_price_currency',
        'created_at', 'updated_at',
    ];
}
