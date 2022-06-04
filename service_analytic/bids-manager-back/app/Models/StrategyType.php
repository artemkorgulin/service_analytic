<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class StrategyType
 *
 * @package App\Models
 *
 * @property string $name
 *
 * @method static find($strategyTypeId)
 */
class StrategyType extends Model
{
    const OPTIMAL_SHOWS = 1; // Оптимальное количество показов
    const OPTIMIZE_CPO  = 2; // Оптимизация по CPO
    /** @var array Данные по моделям счётчиков стратегий, класс и метод связи в модели Strategy для получения
     * или создания записи со счётчиками
     */
    const TYPE_CLASS_DATA = [
        self::OPTIMAL_SHOWS => StrategyShows::class,
        self::OPTIMIZE_CPO => StrategyCpo::class
    ];

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
