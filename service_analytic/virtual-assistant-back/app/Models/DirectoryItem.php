<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectoryItem extends Model
{
    use HasFactory;

    public $fillable = [
        'directory_id', 'value', 'created_at', 'updated_at',
    ];
}
