<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    public $fillable = [
        'title', 'small_description', 'description', 'country_of_origin', 'url', 'meta_title',
        'meta_keywords', 'meta_description', 'created_at', 'updated_at',
    ];
}
