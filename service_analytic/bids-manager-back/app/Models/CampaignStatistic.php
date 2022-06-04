<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Contracts\StatisticInterface;

/**
 * Class CampaignStatistic
 *
 * @package App\Models
 *
 * @property integer $campaign_id
 * @property string  $date
 * @property double  $cost
 * @property integer $shows
 * @property integer $clicks
 * @property integer $orders
 * @property double  $profit
 * @property integer $popularity
 */
class CampaignStatistic extends Model implements StatisticInterface
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'date',
        'cost',
        'shows',
        'clicks',
        'orders',
        'profit',
        'popularity'
    ];

    /**
     * @return BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
