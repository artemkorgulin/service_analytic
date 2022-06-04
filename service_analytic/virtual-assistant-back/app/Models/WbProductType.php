<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbProductType extends Model
{
    use HasFactory;

    public $fillable = [
        'name', 'data',
    ];

    public $casts = [
        'data' => 'object',
    ];
}
