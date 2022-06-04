<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrowAbuse extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'json'
    ];

    protected $fillable = [
        'product_id',
        'site',
        'self_product_link',
        'another_product_link',
        'certificate_link',
        'data'
    ];
}
