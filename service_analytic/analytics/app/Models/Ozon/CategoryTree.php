<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTree extends Model
{
    use HasFactory;

    protected $connection = 'ozon';
}
