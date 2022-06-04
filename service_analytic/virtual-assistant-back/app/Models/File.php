<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'account_id', 'url', 'mime_type', 'title', 'comment', 'created_at', 'updated_at',
    ];
}
