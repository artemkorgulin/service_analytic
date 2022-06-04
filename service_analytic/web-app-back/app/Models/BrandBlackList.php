<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandBlackList extends Model
{
    use HasFactory;

    public $table = 'brand_black_list';

    protected $fillable = ['brand', 'manager', 'status', 'wb', 'ozon', 'pattern'];
}
