<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobNewAccount extends Model
{
    use HasFactory;

    public $table = 'jobs_new_account';

    public $fillable = [
        'account_id', 'platform', 'command_start_at', 'comment',
    ];
}
