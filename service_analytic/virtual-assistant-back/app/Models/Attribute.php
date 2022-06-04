<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'account_id', 'title', 'code', 'required', 'multiple', 'added',
        'facet', 'dictionary_id', 'comment', 'created_at', 'updated_at',
    ];
}
