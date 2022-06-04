<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'account_id', 'url', 'title', 'comment', 'created_at', 'updated_at',
    ];
}
