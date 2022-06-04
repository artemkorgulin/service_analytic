<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzOptionStatItem extends Model
{
    use HasFactory;

    protected $table = 'oz_option_stat_items';

    public $fillable = [
        'option_stat_id', 'category', 'key_request', 'search_response', 'popularity', 'add_to_cart', 'conversion',
        'average_price', 'search_date', 'parsing_datetime', 'created_at', 'updated_at',
    ];
}
