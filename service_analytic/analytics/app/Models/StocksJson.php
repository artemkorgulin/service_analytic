<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocksJson extends Model
{
    use HasFactory;

    protected $casts = [
        'json' => AsCollection::class
    ];
}
