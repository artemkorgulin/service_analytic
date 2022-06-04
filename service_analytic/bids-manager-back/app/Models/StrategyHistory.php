<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StrategyHistory
 *
 * @package App\Models
 *
 * @property integer $strategy_id
 * @property double  $parameter
 * @property double  $value_before
 * @property integer $value_after
 *
 * @method static create(array $array)
 */
class StrategyHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['strategy_id', 'parameter', 'value_before', 'value_after'];

    /**
     * Стратегия
     *
     * @return BelongsTo
     */
    public function strategy()
    {
        return $this->belongsTo(Strategy::class);
    }

    /**
     * @param  Strategy  $strategy
     * @param  array  $oldAttributes
     * @param  array  $newAttributes
     */
    public static function saveStrategyHistory(Strategy $strategy, array $oldAttributes, array $newAttributes, array $fieldsNameFromHistory = [])
    {
        foreach ($oldAttributes as $key => $oldValue) {
            if ($newAttributes[$key] !== $oldValue && (!empty($fieldsNameFromHistory) && in_array($key, $fieldsNameFromHistory))) {
                $history = new StrategyHistory([
                    'strategy_id' => $strategy->id,
                    'parameter' => $key,
                    'value_before' => $oldValue,
                    'value_after' => $newAttributes[$key]
                ]);
                $history->save();
            }
        }
    }
}
