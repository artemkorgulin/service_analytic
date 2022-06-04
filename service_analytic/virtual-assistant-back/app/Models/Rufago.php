<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rufago extends Model
{
    use HasFactory;

    protected $connection = 'analytica';
    protected $table = 'rufago';
}
