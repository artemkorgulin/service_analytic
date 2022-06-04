<?php

namespace App\Models\Remote\VA;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbProduct extends Model
{
    use HasFactory;

    protected $connection = 'va';
}
