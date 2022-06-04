<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbProductStatus extends Model
{
    protected $table = 'wb_product_statuses';

    public $timestamps = false;

    public $fillable = ['code', 'name', 'marketplace_equivalents'];

}
