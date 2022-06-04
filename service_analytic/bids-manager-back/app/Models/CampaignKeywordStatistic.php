<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Contracts\StatisticInterface;

/**
 * Class CampaignKeywordStatistic
 *
 * @package App\Models
 *
 * @property integer $campaign_keyword_id
 * @property string  $date
 * @property double  $cost
 * @property integer $shows
 * @property integer $clicks
 * @property double  $ctr
 * @property integer $orders
 * @property double  $profit
 * @property integer $popularity
 */
class CampaignKeywordStatistic extends Model implements StatisticInterface
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'campaign_keyword_id',
        'date',
        'cost',
        'shows',
        'clicks',
        'ctr',
        'orders',
        'profit',
        'popularity',
    ];

    /**
     * Ключевое слово в РК
     *
     * @return BelongsTo
     */
    public function campaignKeyword()
    {
        return $this->belongsTo(CampaignKeyword::class);
    }
}
