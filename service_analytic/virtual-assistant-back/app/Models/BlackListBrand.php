<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlackListBrand extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        'partner', 'brand', 'manager', 'status', 'wildberries', 'ozon', 'amazon',
    ];

    public $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
