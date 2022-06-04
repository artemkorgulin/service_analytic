<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'account_id', 'parent_id', 'title', 'sorting', 'comment', 'created_at', 'updated_at',
    ];
}
