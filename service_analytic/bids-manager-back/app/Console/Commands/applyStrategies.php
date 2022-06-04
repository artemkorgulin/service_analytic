<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Strategy;
use App\Services\StrategyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class applyStrategies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apply:strategies {campaignIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Применить стратегии к РК';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campaignIds = $this->argument('campaignIds');
        $this->applyStrategies($campaignIds);
    }

    /**
     * Применить стратегии к кампаниям
     *
     * @param int[] $campaignIds
     */
    public function applyStrategies($campaignIds = null)
    {
        echo date('Y-m-d H:i:s').': Start strategies applying'."\r\n";

        // Проверяем, что сегодня уже была синхронизация с сервисами данных
        $campaignsQuery = Campaign::query()
                                  ->when($campaignIds, function(Builder $query) use ($campaignIds) {
                                      $query->whereIn('campaigns.id', $campaignIds);
                                  })
                                  ->join('strategies', 'strategies.campaign_id', '=', 'campaigns.id');

        $lastStatisticDate  = (clone $campaignsQuery)->max('last_ozon_sync');
        $lastPopularityDate = (clone $campaignsQuery)->max('last_vp_sync');

        // и что стратегия еще не применилась по всем РК
        $lastApplyingDate = Strategy::query()
                                    ->when($campaignIds, function(Builder $query) use ($campaignIds) {
                                        $query->whereIn('campaign_id', $campaignIds);
                                    })
                                    ->min('last_applying_date');

        // Сегодня
        $today = Carbon::today()->toDateTimeString();

        echo 'Last Statistic Date: '.$lastStatisticDate."\r\n";
        echo 'Last Popularity Date: '.$lastPopularityDate."\r\n";
        echo 'Last Stategies Date: '.$lastApplyingDate."\r\n";
        echo 'Today: '.$today."\r\n";

        if( $lastStatisticDate >= $today && $lastApplyingDate < $today )
        {
            if ( $lastPopularityDate >= $today )
            {
                echo date('Y-m-d H:i:s').': Applying Optimal Shows Strategy'."\r\n";
                StrategyService::applyOptimalShows($campaignIds);
            }

            echo date('Y-m-d H:i:s').': Applying Optimal CPO Strategy'."\r\n";
            StrategyService::applyOptimalCpo();
        }

        echo date('Y-m-d H:i:s').': Strategies were applied'."\r\n";
    }
}
