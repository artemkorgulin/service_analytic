<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StrategyCpoKeywordStatistic
 *
 * @package App\Models
 *
 * @property integer $strategy_cpo_id
 * @property integer $campaign_keyword_id
 * @property string  $date
 * @property integer $bid
 * @property double  $fcpo
 * @property double  $conversion
 *
 * @property-read StrategyCpo $strategyCpo
 * @property-read Strategy    $strategy
 */
class StrategyCpoKeywordStatistic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'strategy_cpo_id',
        'campaign_keyword_id',
        'conversion',
        'fcpo',
        'date',
        'bid'
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

    /**
     * Ключевое слово
     *
     * @return BelongsTo
     */
    public function campaignKeyword()
    {
        return $this->belongsTo(CampaignKeyword::class);
    }
}
