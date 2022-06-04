<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTop20Features extends Model
{
    use HasFactory;

    protected $table = 'wb_top20_features';

    protected $fillable = [
        'directory_slug', 'directory_id', 'object', 'feature_id', 'feature_title', 'title', 'popularity', 'has_in_ozon',
    ];
}
