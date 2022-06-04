<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbGuideProductCharacteristics extends Model
{
    use HasFactory;

    public $fillable = ['wb_category_id', 'characteristic_name', 'is_require', 'use_frequency', 'output_position'];
}
