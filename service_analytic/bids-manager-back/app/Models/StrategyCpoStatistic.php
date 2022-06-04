<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StrategyCpoStatistic
 *
 * @package App\Models
 *
 * @property integer $strategy_cpo_id
 * @property string  $date
 * @property integer $orders
 * @property double  $fcpo
 * @property double  $conversion
 *
 * @property-read StrategyCpo $strategyCpo
 * @property-read Strategy    $strategy
 */
class StrategyCpoStatistic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'strategy_cpo_id',
        'conversion',
        'fcpo',
        'date',
        'orders'
    ];

    /**
     * Стратегия
     *
     * @return BelongsTo
     */
    public function strategy()
    {
        return $this->strategyCpo->strategy();
    }

    /**
     * Стратегия оптимальных показов
     *
     * @return BelongsTo
     */
    public function strategyCpo()
    {
        return $this->belongsTo(StrategyCpo::class);
    }
}
