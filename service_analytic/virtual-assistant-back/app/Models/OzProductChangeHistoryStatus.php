<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzProductChangeHistoryStatus extends Model
{
    use HasFactory;

    public $fillable = [
        'history_id', 'response_data', 'request_data', 'text_status', 'status_id', 'created_at', 'updated_at',
    ];


    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];
}
