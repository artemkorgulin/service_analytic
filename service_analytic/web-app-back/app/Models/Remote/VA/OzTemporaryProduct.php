<?php

namespace App\Models\Remote\VA;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzTemporaryProduct extends Model
{
    use HasFactory;

    protected $connection = 'va';
}
