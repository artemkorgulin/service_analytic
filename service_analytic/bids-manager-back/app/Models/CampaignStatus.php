<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CampaignStatus
 *
 * @package App\Models
 *
 * @property string $code
 * @property string $ozon_code
 * @property string $name
 *
 * @method static create(array $array)
 */
class CampaignStatus extends Model
{
    const ACTIVE   = 'RUNNING';
    const INACTIVE = 'INACTIVE';
    const PLANNED  = 'PLANNED';
    const STOPPED  = 'STOPPED';
    const DRAFT    = 'DRAFT';
    const ARCHIVED = 'ARCHIVED';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'ozon_code',
        'name',
    ];
}
