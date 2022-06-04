<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzCategoryTree extends Model
{
    use HasFactory;

    protected $connection = 'parser';
    protected $table = 'category_trees';
}
