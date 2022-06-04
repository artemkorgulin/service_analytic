<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EscrowHash extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'image_hash', 'product_id', 'nmid'];
}
