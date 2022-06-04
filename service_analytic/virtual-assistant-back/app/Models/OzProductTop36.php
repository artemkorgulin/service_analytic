<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzProductTop36 extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 'parsed_at', 'web_category_id', 'type', 'price', 'review_count', 'rating', 'photo_count',
        'created_at', 'updated_at',
    ];
}
