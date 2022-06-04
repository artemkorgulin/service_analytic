<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSemanticsRufago extends Model
{
    use HasFactory;
    use HasFactory;

    protected $table = 'platform_semantic_rufago';

    protected $fillable = [
        'wb_product_id','wb_product_name', 'wb_parent_id', 'wb_parent_name', 'oz_category_id', 'oz_category_name',
        'key_request', 'search_response', 'popularity'
    ];
}
