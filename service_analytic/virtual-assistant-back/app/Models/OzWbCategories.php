<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzWbCategories extends Model
{
    use HasFactory;

    protected $fillable = [
        'rufago_ids','oz_name', 'wb_name', 'oz_category_id', 'oz_parent_id', 'wb_category_id', 'wb_parent_id',
        'rufago_subject_id', 'search', 'search_response'
    ];
}
