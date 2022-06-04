<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptimisationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'content_percent',
        'search_percent',
        'visibility_percent',
        'report_date',
        'account_id'
    ];
}
