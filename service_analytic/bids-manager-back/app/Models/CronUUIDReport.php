<?php

namespace App\Models;

use App\DataTransferObjects\AccountDTO;
use App\DataTransferObjects\Services\OzonPerformance\CampaignStatistics\CampaignStatisticsDTO;
use App\Enums\OzonPerformance\Campaign\CampaignReportState;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CronUUIDReport
 *
 * @package App\Models
 *
 * @property string $uuid
 * @property integer $account_id
 * @property boolean $processed
 * @property CampaignReportState $state
 * @property Carbon $date_from
 * @property Carbon $date_to
 * @property array $campaign_ids
 * @property array $files
 * @method Builder unprocessed()
 */
class CronUUIDReport extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cron_uuid_report';

    protected $casts = [
        'date_from'    => 'datetime',
        'date_to'      => 'datetime',
        'state'        => CampaignReportState::class,
        'files'        => 'array',
        'campaign_ids' => 'array'
    ];


    /**
     * @param  CampaignStatisticsDTO  $requestResult
     * @param  AccountDTO  $account
     * @param  CarbonImmutable|Carbon  $dateFrom
     * @param  \Carbon\CarbonImmutable|\Carbon\Carbon  $dateTo
     * @param  array  $ids
     *
     * @return bool
     */
    public static function saveReportRequest(
        CampaignStatisticsDTO $requestResult,
        AccountDTO $account,
        CarbonImmutable|Carbon $dateFrom,
        CarbonImmutable|Carbon $dateTo,
        array $ids
    ): bool
    {
        $cronUUIDReport               = new CronUUIDReport();
        $cronUUIDReport->uuid         = $requestResult->UUID;
        $cronUUIDReport->account_id   = $account->id;
        $cronUUIDReport->date_from    = $dateFrom->toMutable();
        $cronUUIDReport->date_to      = $dateTo->toMutable();
        $cronUUIDReport->campaign_ids = $ids;

        return $cronUUIDReport->save();
    }


    /*** Scopes ***/

    public function scopeUnprocessed(Builder $query)
    {
        $query->where('processed', false);
    }
}
