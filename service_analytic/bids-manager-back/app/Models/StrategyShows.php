<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StrategyShows
 *
 * @package App\Models
 *
 * @property integer $strategy_id
 * @property double  $threshold
 * @property double  $step
 */
class StrategyShows extends Model
{
    const DAYS_IN_HORIZON = 10; // Кол-во дней горизонта оценки по умолчанию
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'strategies_shows';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['strategy'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['strategy_id', 'threshold', 'step'];

    /**
     * Стратегия
     *
     * @return BelongsTo
     */
    public function strategy()
    {
        return $this->belongsTo(Strategy::class);
    }
}
