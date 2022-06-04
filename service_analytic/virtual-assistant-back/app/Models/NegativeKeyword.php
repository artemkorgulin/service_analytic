<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NegativeKeyword
 *
 * @package App\Models
 *
 * @property string $name
 */
class NegativeKeyword extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

}
