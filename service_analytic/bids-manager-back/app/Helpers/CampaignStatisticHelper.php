<?php

namespace App\Helpers;

use App\Models\Campaign;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class CampaignStatisticHelper extends StatisticHelper
{
    /**
     * Сформировать запрос фильтрации
     *
     * @param  Request $request
     *
     * @return Builder
     */
    public static function makeFilterQuery(Request $request)
    {
        $accountId = UserService::getCurrentAccountId();
        $from = $request->from ?? null;
        $to =  $request->to ?? null;
        $campaignIds = $request->campaigns;
        $statusesIds = $request->campaignsStatuses;
        $placementsIds = $request->placements;
        $strategiesTypesIds = $request->strategiesTypes;
        $strategiesStatusesIds = $request->strategiesStatuses;

        $query =  Campaign::query()
                        ->leftJoin('campaign_statistics', function (JoinClause $join) use ($from, $to) {
                           $join->on('campaign_statistics.campaign_id', '=', 'campaigns.id')
                           ->when($from, function(JoinClause $query) use ($from) {
                               $query->whereDate('campaign_statistics.date', '>=', $from);
                           })
                           ->when($to, function(JoinClause $query) use ($to) {
                               $query->whereDate('campaign_statistics.date', '<=', $to);
                           });
                       })
                       ->join('campaign_statuses', 'campaign_statuses.id', '=', 'campaigns.campaign_status_id')
                       ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
                       ->join('campaign_payment_types', 'campaign_payment_types.id', '=', 'campaigns.payment_type_id')
                       ->join('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
                       ->leftJoin('strategies', 'campaigns.id', '=', 'strategies.campaign_id')
                       ->leftJoin('strategy_types', 'strategies.strategy_type_id', '=', 'strategy_types.id')
                       ->leftJoin('strategy_statuses', 'strategies.strategy_status_id', '=', 'strategy_statuses.id')

                        ->when($campaignIds, function(Builder $query) use ($campaignIds) {
                            $query->whereIn('campaign_products.campaign_id', $campaignIds);
                        })
                        ->when($statusesIds, function(Builder $query) use ($statusesIds) {
                            $query->whereIn('campaigns.campaign_status_id', $statusesIds);
                        })
                        ->when($placementsIds, function(Builder $query) use ($placementsIds) {
                            $query->whereIn('campaigns.placement_id', $placementsIds);
                        })
                        ->when($strategiesTypesIds, function(Builder $query) use ($strategiesTypesIds) {
                            $query->whereIn('strategies.strategy_type_id', $strategiesTypesIds);
                        })
                        ->when($strategiesStatusesIds, function(Builder $query) use ($strategiesStatusesIds) {
                            $query->whereIn('strategies.strategy_status_id', $strategiesStatusesIds);
                        })
                       ->where('campaigns.account_id', $accountId)

                        ->distinct();
//                      TODO  replace Constants::CAMPAIGN_SEARCH to  CAMPAIGN_TYPES_SEARCH
//                      ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
//                      ->where('campaign_page_types.name', Constants::CAMPAIGN_SEARCH)
                       /*->whereDate('campaign.created_at', '<=', $to)*/
        return  $query;
    }

    /**
     * Сформировать запрос детальной статистики
     *
     * @param  Request $request
     *
     * @return Builder|mixed
     */
    public static function makeDetailedStatisticQuery(Request $request)
    {
        $query = self::makeFilterQuery($request);

        $detailedQuery = $query
                            ->select([
                                'campaigns.id as campaign_id',
                                'campaigns.ozon_id as campaign_ozon_id',
                                'campaigns.name as campaign_name',
                                'campaigns.start_date as campaign_start_date',
                                'campaigns.end_date as campaign_end_date',
                                'campaign_page_types.name as campaign_page_type_name',
                                'campaigns.campaign_status_id as campaign_status_id',
                                'campaign_statuses.name as campaign_status_name',
                                'campaigns.payment_type_id as campaigns_payment_type_id',
                                'campaign_payment_types.name as campaigns_payment_type'
                            ])
                            ->selectRaw('campaigns.budget as daily_budget')
                            ->addSelect(
                                'strategies.id as strategy_id',
                                'strategies.strategy_type_id',
                                'strategy_types.name as strategy_name',
                                'strategy_types.behavior as strategy_behavior',
                                'strategies.strategy_status_id',
                                'strategy_statuses.name as strategy_status_name'
                            )
                            ->selectRaw('DATEDIFF(CURTIME(), strategies.updated_at) as strategy_days_since_last_change')
                            ->selectRaw('
                                (
                                    CASE WHEN
                                        CURTIME() >= campaigns.start_date && CURTIME() <= campaigns.end_date
                                    THEN
                                        DATEDIFF(campaigns.end_date, CURTIME())
                                    ELSE 0
                                    END
                                )
                                as campaign_days_before_the_end
                            ');

        return self::addCharacteristicsSelect($detailedQuery, 'campaign_statistics')
                   ->orderBy('campaign_statuses.sort', 'asc')
                   ->orderBy('campaigns.id', 'desc')
                   ->groupBy('campaigns.id');
    }

    /**
     * Сформировать запрос итогов статистики
     *
     * @param Request $request
     *
     * @return Builder|mixed
     */
    public static function makeStatisticTotalsQuery(Request $request)
    {
        $query = self::makeFilterQuery($request);

        return self::addCharacteristicsSelect($query, 'campaign_statistics');
    }
}
