<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OzFeatureOption
 *
 * @package App\Models
 */
class OzFeatureOption extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'value',
        'id',
        'popularity'
    ];
}
