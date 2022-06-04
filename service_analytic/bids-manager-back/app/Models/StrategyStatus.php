<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class StrategyStatus
 *
 * @package App\Models
 *
 * @property string $name
 */
class StrategyStatus extends Model
{
    const ACTIVE = 1;
    const NOT_ACTIVE = 2;

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
    protected $fillable = ['name'];

    /**
     * Стратегии
     *
     * @return HasMany
     */
    public function strategies()
    {
        return $this->hasMany(Strategy::class);
    }
}
