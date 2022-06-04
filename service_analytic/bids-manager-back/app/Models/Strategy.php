<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Strategy
 *
 * @package App\Models
 *
 * @property integer $strategy_type_id
 * @property integer $strategy_status_id
 * @property integer $campaign_id
 * @property string  $created_at
 * @property string  $activated_at
 *
 * @property-read Campaign       $campaign
 * @property-read StrategyType   $strategyType
 * @property-read StrategyStatus $strategyStatus
 *
 * @method static find($strategyId)
 */
class Strategy extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['strategy_type_id', 'strategy_status_id', 'activated_at'];

    /**
     * Тип стратегии
     *
     * @return BelongsTo
     */
    public function strategyType()
    {
        return $this->belongsTo(StrategyType::class);
    }

    /**
     * Статус стратегии
     *
     * @return BelongsTo
     */
    public function strategyStatus()
    {
        return $this->belongsTo(StrategyStatus::class);
    }

    /**
     * Рекламная кампания стратегии
     *
     * @return HasOne
     */
    public function campaign()
    {
        return $this->hasOne(Campaign::class);
    }

    /**
     * Стратегия Оптимального количества показов
     *
     * @return HasOne
     */
    public function strategyShows()
    {
        return $this->hasOne(StrategyShows::class);
    }

    /**
     * Стратегия Оптимизация по CPO
     *
     * @return HasOne
     */
    public function strategyCpo()
    {
        return $this->hasOne(StrategyCpo::class);
    }

    /**
     * Активировать стратегию
     */
    public function activate()
    {
        $this->update(['strategy_status_id' => StrategyStatus::ACTIVE]);
    }

    /**
     * Деактивировать аккаунт
     */
    public function deactivate()
    {
        $this->update(['strategy_status_id' => StrategyStatus::NOT_ACTIVE]);
    }

    public static function saveStrategy(array $data, Campaign $campaign)
    {
        if (empty($campaign->strategy)) {
            $strategy = new Strategy([
                'strategy_type_id' => $data['strategy_type_id'],
                'strategy_status_id' => StrategyStatus::ACTIVE
            ]);
            $strategy->campaign_id = $campaign->id;
        } else {
            $strategy = $campaign->strategy;
        }

        if (!empty($data['strategy_type_id']) && $strategy->strategy_type_id !== $data['strategy_type_id']) {
            $strategy->activated_at = null;
        }

        $strategy->strategy_type_id = $data['strategy_type_id'] ?? $strategy->strategy_type_id;
        $strategy->strategy_status_id = $data['strategy_status_id'] ?? $strategy->strategy_status_id;
        $strategy->save();
        $strategy->saveStrategyCount($data);

        return $strategy;
    }

    public function saveStrategyCount(array $data)
    {
        $typeClass = StrategyType::TYPE_CLASS_DATA[$this->strategy_type_id];
        $model = $typeClass::where('strategy_id', '=', $this->id)->first();

        if (empty($model)) {
            $model = new $typeClass(array_merge(['strategy_id' => $this->id], $data));
            $model->save();
        } else {
            $oldAttributes = $model->getAttributes();
            $model->fill($data);
            $model->save();
            $newAttributes = $model->getAttributes();
            StrategyHistory::saveStrategyHistory($this, $oldAttributes, $newAttributes, $model->getFillable());
        }

        return $model;
    }
}
