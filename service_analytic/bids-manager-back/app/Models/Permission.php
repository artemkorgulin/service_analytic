<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Permission
 *
 * @package App\Models
 *
 * @property string $code
 * @property string $name
 */
class Permission extends Model
{
    const AD_CAMPAIGNS = 'AD_CAMPAIGNS';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
