<?php

namespace App\Models\Analytica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserParams extends Model
{
    use HasFactory;

    protected $connection = 'analytica';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'url',
        'params',
    ];
}
