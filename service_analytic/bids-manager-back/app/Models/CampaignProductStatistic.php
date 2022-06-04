<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Contracts\StatisticInterface;

/**
 * Class CampaignProductStatistic
 *
 * @package App\Models
 *
 * @property integer $campaign_product_id
 * @property string $date
 * @property double $cost
 * @property integer $shows
 * @property integer $clicks
 * @property double $ctr
 * @property integer $orders
 * @property double $profit
 * @property integer $popularity
 */
class CampaignProductStatistic extends Model implements StatisticInterface
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'campaign_product_id',
        'date',
        'cost',
        'shows',
        'clicks',
        'orders',
        'profit',
        'popularity',
    ];
    protected $table = 'campaign_product_statistic';

    /**
     * Товар
     *
     * @return BelongsTo
     */
    public function campaignProduct()
    {
        return $this->belongsTo(CampaignProduct::class);
    }
}
