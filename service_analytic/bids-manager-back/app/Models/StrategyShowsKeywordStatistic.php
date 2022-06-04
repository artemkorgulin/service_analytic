<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StrategyShowsKeywordStatistic
 *
 * @package App\Models
 *
 * @property integer $strategy_shows_id
 * @property integer $campaign_keyword_id
 * @property string  $date
 * @property integer $bid
 * @property integer $popularity
 * @property double  $threshold
 * @property double  $step
 * @property double  $avg_shows
 * @property double  $avg_popularity
 * @property double  $threshold_popularity
 *
 * @property-read StrategyShows $strategyShows
 * @property-read Strategy      $strategy
 */
class StrategyShowsKeywordStatistic extends Model
{
    /**
     * Стратегия
     *
     * @return BelongsTo
     */
    public function strategy()
    {
        return $this->strategyShows->strategy();
    }

    /**
     * Стратегия оптимальных показов
     *
     * @return BelongsTo
     */
    public function strategyShows()
    {
        return $this->belongsTo(StrategyShows::class);
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
