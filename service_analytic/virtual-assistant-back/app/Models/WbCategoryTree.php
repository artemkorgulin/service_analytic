<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbCategoryTree extends Model
{
    use HasFactory;

    protected $connection = 'analytica';
    protected $table = 'category_trees';
}
