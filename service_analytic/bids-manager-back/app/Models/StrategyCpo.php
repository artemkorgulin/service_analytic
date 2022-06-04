<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StrategyCpo
 *
 * @package App\Models
 *
 * @property integer $strategy_id
 * @property integer $tcpo
 * @property integer $vr
 * @property integer $horizon
 * @property integer $threshold1
 * @property integer $threshold2
 * @property integer $threshold3
 */
class StrategyCpo extends Model
{
    const MIN_ORDERS      = 50; // Минимальное кол-во заказов
    const DAYS_IN_HORIZON = 30; // Кол-во дней горизонта оценки по умолчанию
    const MIN_BUDGET      = 500; // Минимальный бюджет для рекламной кампании
    const MIN_THRESHOLD   = 50; // Минимальный порог принятия решения
    const STEP_THRESHOLD  = 50; // Минимальный шаг между порогами принятия решения
    const MIN_TCPO        = 100; // Минимальное значение целевого CPO
    const MIN_VR          = 5; // Минимальный коэффициент волатильности
    const MAX_VR          = 30; // Максимальный коэффициент волатильности

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'strategies_cpo';

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
    protected $fillable = ['strategy_id', 'tcpo', 'vr', 'horizon', 'threshold1', 'threshold2', 'threshold3'];

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
