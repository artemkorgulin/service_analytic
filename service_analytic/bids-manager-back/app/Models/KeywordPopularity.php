<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class KeywordPopularity
 *
 * @package App\Models
 */
class KeywordPopularity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'keyword_id',
        'date',
        'popularity',
    ];
}
