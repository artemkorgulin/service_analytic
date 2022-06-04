<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzOptionFrequency extends Model
{
    use HasFactory;

    public $fillable = [
        'option_value', 'option_value_count', 'created_at', 'updated_at'
    ];



}
