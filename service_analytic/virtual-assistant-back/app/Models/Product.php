<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'account_id', 'type_id', 'brand_id', 'title', 'sku', 'sku_manufacturer',
        'barcode', 'guid', 'small_description', 'description', 'meta_title', 'meta_keywords',
        'meta_description', 'retail_price', 'retail_price_currency', 'sale_price',
        'sale_price_currency', 'whosale_price', 'whosale_price_currency', 'weight',
        'weight_units', 'length', 'width', 'height', 'length_units', 'volume',
        'shippable', 'available', 'created_at', 'updated_at',
    ];



}
