<?php

namespace App\Helpers;

use App\Models\Strategy;
use App\Models\StrategyCpo;
use App\Models\StrategyHistory;
use App\Models\StrategyShowsKeywordStatistic;
use App\Models\StrategyShows;
use App\Models\StrategyStatus;
use App\Models\StrategyType;
use App\Services\CalculateService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class StrategyHelper
{
    /**
     * Получить данные для ответа
     *
     * @param Strategy $strategy
     *
     * @return array
     */
    public static function getDataResponse($strategy)
    {
        $response = [
            'strategyTypeId' => $strategy->strategy_type_id,
            'strategyId' => $strategy->id,
            'strategyName' => $strategy->strategyType->name,
            'strategyBehavior' => $strategy->strategyType->behavior,
            'campaignId' => $strategy->campaign_id,
            'campaignName' => $strategy->campaign->name,
            'campaignBudget' => $strategy->campaign->budget,
            'strategyLastChangedDate' => $strategy->updated_at->format('d.m.Y'),
        ];
        switch( $strategy->strategy_type_id ) {
            case StrategyType::OPTIMAL_SHOWS:
                return array_merge($response, [
                    'strategyThreshold' => $strategy->strategyShows->threshold,
                    'strategyStep' => $strategy->strategyShows->step
                ]);
            case StrategyType::OPTIMIZE_CPO:
                return array_merge($response, [
                    'strategyThreshold1' => $strategy->strategyCpo->threshold1,
                    'strategyThreshold2' => $strategy->strategyCpo->threshold2,
                    'strategyThreshold3' => $strategy->strategyCpo->threshold3,
                    'strategyTcpo' => $strategy->strategyCpo->tcpo,
                    'strategyVr' => $strategy->strategyCpo->vr
                ]);
        }

        return $response;
    }

    /**
     * Сформировать запрос детальной таблицы
     *
     * @param integer $strategyTypeId
     * @param string  $sortBy
     * @param string  $sortDir
     *
     * @return Builder|mixed
     */
    public static function makeStrategyCampaignsQuery($strategyTypeId, $sortBy, $sortDir)
    {
        switch( $strategyTypeId ) {
            case StrategyType::OPTIMAL_SHOWS:
                $sortBy = $sortBy === 'change_date' ? 'updated_at' : $sortBy;
                $query = self::makeStrategyOptimalShowsQuery($strategyTypeId);
                break;
            case StrategyType::OPTIMIZE_CPO:
                $query = self::makeStrategyOptimizeCpoShowsQuery($strategyTypeId);
                break;
            default:
                $query = self::makeStrategyQuery();
        }

        return $query
               ->orderBy($sortBy, $sortDir)
               ->orderBy('campaign_id', 'desc');
    }

    /**
     * Список стратегий (универсальный)
     *
     * @return Builder
     */
    protected static function makeStrategyQuery()
    {
        return Strategy::query()
                       ->join('campaigns', 'strategies.campaign_id', '=', 'campaigns.id')
                       ->join('strategy_statuses', 'strategies.strategy_status_id', '=', 'strategy_statuses.id')
                       ->where('campaigns.account_id', UserService::getCurrentAccountId())
                       ->select(
                           'strategies.id',
                           'strategies.strategy_type_id',
                           'strategies.campaign_id',
                           'campaigns.name as campaign_name',
                           'strategies.strategy_status_id',
                           'strategy_statuses.name as strategy_status_name',
                       )
                       ->selectRaw('DATE_FORMAT(strategies.activated_at, "%d.%m.%Y") as activation_date')
                       ->selectRaw('DATE_FORMAT(DATE(strategies.updated_at), "%d.%m.%Y") as change_date')
                       ->selectRaw('DATEDIFF(CURTIME(), strategies.updated_at) as days_since_last_change');
    }

    /**
     * Список стратегий оптимального количества показов
     *
     * @param integer $strategyTypeId
     * @return Builder
     */
    protected static function makeStrategyOptimalShowsQuery($strategyTypeId)
    {
        return StrategyShows::query()
                            ->join('strategies', 'strategies_shows.strategy_id', '=', 'strategies.id')
                            ->join('campaigns', 'strategies.campaign_id', '=', 'campaigns.id')
                            ->join('strategy_statuses', 'strategies.strategy_status_id', '=', 'strategy_statuses.id')
                            ->where([
                                ['campaigns.account_id', UserService::getCurrentAccountId()],
                                ['strategies.strategy_type_id', $strategyTypeId],
                            ])
                            ->select(
                                'strategies_shows.id as strategy_shows_id',
                                'strategies.id',
                                'strategies.campaign_id',
                                'strategies.updated_at',
                                'campaigns.name as campaign_name',
                                'strategies.strategy_status_id',
                                'strategy_statuses.name as strategy_status_name',
                                'strategies_shows.threshold',
                                'strategies_shows.step',
                            )
                            ->selectRaw('DATE_FORMAT(strategies.activated_at, "%d.%m.%Y") as activation_date')
                            ->selectRaw('DATE_FORMAT(DATE(strategies.updated_at), "%d.%m.%Y") as change_date')
                            ->selectRaw('DATEDIFF(CURTIME(), strategies.updated_at) as days_since_last_change')
                            ->without('strategy');
    }

    /**
     * Список стратегий оптимальной цены клика
     *
     * @param integer $strategyTypeId
     * @return Builder
     */
    protected static function makeStrategyOptimizeCpoShowsQuery($strategyTypeId)
    {
        return StrategyCpo::query()
            ->join('strategies', 'strategies_cpo.strategy_id', '=', 'strategies.id')
            ->join('campaigns', 'strategies.campaign_id', '=', 'campaigns.id')
            ->Leftjoin('campaign_products', 'campaigns.id', 'campaign_products.campaign_id')
            ->Leftjoin('campaign_keywords', 'campaign_products.id', 'campaign_keywords.campaign_product_id')
            ->Leftjoin('campaign_keyword_statistics', function(JoinClause $join) {
                $join->on('campaign_keyword_statistics.campaign_keyword_id', 'campaign_keywords.id')
                    ->whereRaw('DATEDIFF(CURTIME(), campaign_keyword_statistics.date) <= strategies_cpo.horizon');
            })
            ->join('strategy_statuses', 'strategies.strategy_status_id', '=', 'strategy_statuses.id')
            ->where([
                ['campaigns.account_id', UserService::getCurrentAccountId()],
                ['strategies.strategy_type_id', $strategyTypeId],
            ])
            ->select(
                'strategies_cpo.id as strategies_cpo_id',
                'strategies.id',
                'strategies.campaign_id',
                'campaigns.name as campaign_name',
                'strategies.strategy_status_id',
                'strategy_statuses.name as strategy_status_name',
                'strategies_cpo.tcpo',
                DB::raw('ROUND((SUM(campaign_keyword_statistics.cost) / SUM(campaign_keyword_statistics.orders)), 2) as fcpo'),
                'strategies_cpo.vr',
                'strategies_cpo.horizon',
                'strategies_cpo.threshold1',
                'strategies_cpo.threshold2',
                'strategies_cpo.threshold3',
            )
            ->selectRaw('DATE_FORMAT(strategies.activated_at, "%d.%m.%Y") as activation_date')
            ->selectRaw('DATE_FORMAT(DATE(strategies.updated_at), "%d.%m.%Y") as change_date')
            ->selectRaw('DATEDIFF(CURTIME(), strategies.updated_at) as days_since_last_change')
            ->groupBy('strategies_cpo.strategy_id')
            ->without('strategy');
    }

    /**
     * Сформировать запрос детальной таблицы
     *
     * @param Strategy $strategy
     * @param bool     $needReformat
     * @param string   $group
     * @param int[]    $keywordsIds
     * @param string   $from
     * @param string   $to
     * @param array    $paginate
     *
     * @return array
     */
    public static function getStrategyKeywordsHistory($strategy, $needReformat, $group = null, $keywordsIds = null, $from = null, $to = null, $paginate = null)
    {
        switch( $strategy->strategy_type_id ) {
            case StrategyType::OPTIMAL_SHOWS:
                if( is_null($from) ) {
                    $daysCount = 30;
                    $dateFrom = Carbon::now()->subDays($daysCount);
                } else {
                    $dateFrom = Carbon::parse($from);
                }

                if( is_null($to) ) {
                    $dateTo = Carbon::now();
                } else {
                    $dateTo = Carbon::parse($to);
                }
                $query   = self::makeStrategyOptimalShowsHistoryQuery($strategy->id, $group, $keywordsIds, $dateFrom, $dateTo);
                $history = $paginate['all']
                    ? $query->get()
                    : $query->paginate($paginate['per_page'],  ['*'], 'page', $paginate['current_page']);

                foreach( $history as &$historyRecord )
                {
                    CalculateService::recalcAllCharacteristicsForStrategyHistory($historyRecord);
                    if( $needReformat ) CalculateService::reassignStrategyColumns($historyRecord);
                }
                break;

            case StrategyType::OPTIMIZE_CPO:
                if (!$from && !$to) {
                    $defaultHorizon = self::getDefaultHorizonStrategyCpo($strategy->id);
                    $dateFrom       = $defaultHorizon['from'];
                    $dateTo         = $defaultHorizon['to'];
                }else{
                    $dateFrom = Carbon::parse($from);
                    $dateTo = Carbon::parse($to);
                }
                $historyQuery = self::makeStrategyCpoHistoryQuery($strategy->id, $group, $keywordsIds, $dateFrom, $dateTo, true)
                    ->groupBy('campaign_keywords.id')
                    ->orderBy('groups.ozon_id', $paginate['order'])
                    ->orderBy('products.id', 'desc')
                    ->orderBy('campaign_keywords.status_id', 'asc')
                    ->orderBy('campaign_keywords.updated_at', 'desc');

                $history = $paginate['all']
                    ? $historyQuery->get()
                    : $historyQuery->paginate($paginate['per_page'],  ['*'], 'page', $paginate['current_page']);

                if ( !$paginate['all'] ) {
                    $dashboard = self::makeStrategyCpoHistoryQuery($strategy->id, $group, $keywordsIds, $dateFrom, $dateTo)->first();
                    if( $needReformat ) CalculateService::reassignStrategyCpoValues($dashboard);
                }

                foreach( $history as &$historyRecord )
                {
                    if( $needReformat ) CalculateService::reassignStrategyCpoValues($historyRecord);
                }

                break;
        }

        return [
            'dashboard' => $dashboard ?? [],
            'history'   => $history ?? []
        ];
    }

    /**
     * Сформировать запрос детальной таблицы стратегии оптимального количества показов
     *
     * @param integer $strategyId
     * @param string  $group
     * @param int[]   $keywordsIds
     * @param Carbon  $dateFrom
     * @param Carbon  $dateTo
     *
     * @return bool|Builder
     */
    protected static function makeStrategyOptimalShowsHistoryQuery($strategyId, $group, $keywordsIds, $dateFrom, $dateTo)
    {
        return StrategyShowsKeywordStatistic::query()
                                            ->join('strategies_shows', 'strategy_shows_keyword_statistics.strategy_shows_id', '=', 'strategies_shows.id')
                                            ->join('strategies', 'strategies_shows.strategy_id', '=', 'strategies.id')
                                            ->leftJoin('campaign_keywords', function(JoinClause $join) {
                                                $join->on('strategy_shows_keyword_statistics.campaign_keyword_id', '=', 'campaign_keywords.id');
                                            })
                                            ->leftJoin('campaign_keyword_statistics', function(JoinClause $join) {
                                                $join->on('campaign_keyword_statistics.campaign_keyword_id', '=', 'strategy_shows_keyword_statistics.campaign_keyword_id')
                                                     ->on('campaign_keyword_statistics.date', '=', 'strategy_shows_keyword_statistics.date');
                                            })
                                            ->where('strategies.id', $strategyId)
                                            ->when($group, function(Builder $query) use ($group) {
                                                if ((int)$group > 0 && (int)$group == $group) {
                                                    $query->where('campaign_keywords.group_id', $group);
                                                } elseif ($group === 'without') {
                                                    $query->whereNull('campaign_keywords.group_id');
                                                }
                                            })
                                            ->when($keywordsIds, function(Builder $query) use ($keywordsIds) {
                                                $query->whereIn('strategy_shows_keyword_statistics.campaign_keyword_id', $keywordsIds);
                                            })
                                            ->whereDate('strategy_shows_keyword_statistics.date', '>=', $dateFrom)
                                            ->whereDate('strategy_shows_keyword_statistics.date', '<=', $dateTo)
                                            ->selectRaw('DATE_FORMAT(strategy_shows_keyword_statistics.date, "%d.%m.%Y") as date')
                                            ->selectRaw('AVG(strategy_shows_keyword_statistics.avg_popularity) AS avg_popularity')
                                            ->selectRaw('AVG(strategy_shows_keyword_statistics.avg_shows) AS avg_shows')
                                            ->selectRaw('(100 * avg_shows / avg_popularity) as avg_purchased_shows')
                                            ->selectRaw('SUM(campaign_keyword_statistics.popularity) AS popularity')
                                            ->when($keywordsIds && count($keywordsIds) == 1, function(Builder $query) {
                                                $query->selectRaw('strategy_shows_keyword_statistics.bid');
                                            })
                                            ->selectRaw('SUM(campaign_keyword_statistics.shows) AS shows')
                                            ->selectRaw('strategy_shows_keyword_statistics.threshold')
                                            ->selectRaw('strategy_shows_keyword_statistics.step')
                                            ->selectRaw('(shows / strategy_shows_keyword_statistics.popularity) AS purchased_shows')
                                            ->selectRaw('SUM(campaign_keyword_statistics.clicks) AS clicks')
                                            ->selectRaw('(100 * clicks / shows) as ctr')
                                            ->selectRaw('(1000 * cost / shows) as avg_1000_shows_price')
                                            ->selectRaw('(cost / clicks) as avg_click_price')
                                            ->selectRaw('SUM(campaign_keyword_statistics.cost) AS cost')
                                            ->selectRaw('SUM(campaign_keyword_statistics.orders) AS orders')
                                            ->selectRaw('SUM(campaign_keyword_statistics.profit) AS profit')
                                            ->selectRaw('(cost / orders) as cpo')
                                            ->selectRaw('(cost / profit) as acos')
                                            ->groupBy('strategy_shows_keyword_statistics.date')
                                            ->orderBy('strategy_shows_keyword_statistics.date', 'desc');
    }

    /**
     * Сформировать запрос детальной таблицы стратегии оптимальной цены клика
     *
     * @param integer $strategyId
     * @param string  $group
     * @param int[]   $keywordsIds
     * @param Carbon  $dateFrom
     * @param Carbon  $dateTo
     * @param bool    $history
     *
     * @return bool|Builder
     */
    protected static function makeStrategyCpoHistoryQuery($strategyId, $group, $keywordsIds, $dateFrom, $dateTo, $history = false)
    {
        return Strategy::query()
            ->where('strategies.id', $strategyId)
            ->join('campaigns', 'strategies.campaign_id', 'campaigns.id')
            ->join('campaign_products', 'campaigns.id', 'campaign_products.campaign_id')
            ->join('campaign_keywords', 'campaign_products.id', 'campaign_keywords.campaign_product_id')
            ->join('campaign_keyword_statistics', function(JoinClause $join) use ($dateFrom, $dateTo) {
                $join->on('campaign_keyword_statistics.campaign_keyword_id', 'campaign_keywords.id')
                    ->where([
                        ['campaign_keyword_statistics.date', '>=', $dateFrom],
                        ['campaign_keyword_statistics.date', '<=', $dateTo]
                    ]);
            })
            ->when($group, function(Builder $query) use ($group) {
                if ((int)$group > 0 && (int)$group == $group) {
                    $query->where('campaign_keywords.group_id', $group);
                } elseif ($group === 'without') {
                    $query->whereNull('campaign_keywords.group_id');
                }
            })
            ->when($keywordsIds, function(Builder $query) use ($keywordsIds) {
                $query->whereIn('campaign_keywords.id', $keywordsIds);
            })
            ->when($history, function(Builder $query) {
                $query->join('keywords', 'campaign_keywords.keyword_id', 'keywords.id')
                    ->join('campaign_statuses', 'campaign_keywords.status_id', 'campaign_statuses.id')
                    ->leftJoin('products', 'campaign_products.product_id', 'products.id')
                    ->leftJoin('groups', 'campaign_keywords.group_id', 'groups.id')
                    ->leftJoin('statuses', 'campaign_keywords.status_id', 'statuses.id')
                    ->select(
                        'keywords.name as keyword_name',
                        'campaign_keywords.status_id as keyword_status_id',
                        'statuses.name as status_name',
                        'groups.name as group_name',
                        'groups.ozon_id as ozon_id',
                        'campaign_keywords.group_id as group_id',
                        'campaign_keywords.id as id',
                        'products.sku as sku'
                    );
            })
            ->selectRaw('SUM(campaign_keyword_statistics.shows) AS shows')
            ->selectRaw('(SUM(campaign_keyword_statistics.orders) / (SUM(campaign_keyword_statistics.shows) / 1000)) AS kvcr')
            ->selectRaw('SUM(campaign_keyword_statistics.clicks) AS clicks')
            ->selectRaw('SUM(campaign_keyword_statistics.clicks) / SUM(campaign_keyword_statistics.shows) * 100 AS ctr')
            ->selectRaw('SUM(campaign_keyword_statistics.cost) / SUM(campaign_keyword_statistics.clicks) AS avg_click_price')
            ->selectRaw('SUM(campaign_keyword_statistics.cost) / SUM(campaign_keyword_statistics.shows) * 1000 AS avg_1000_shows_price')
            ->selectRaw('SUM(campaign_keyword_statistics.cost) AS cost')
            ->selectRaw('SUM(campaign_keyword_statistics.orders) AS orders')
            ->selectRaw('SUM(campaign_keyword_statistics.profit) AS profit')
            ->selectRaw('(SUM(campaign_keyword_statistics.cost) / SUM(campaign_keyword_statistics.orders)) as fcpo')
            ->selectRaw('(SUM(campaign_keyword_statistics.cost) / SUM(campaign_keyword_statistics.profit)) as acos');
    }

    /**
     * Получить горизонт оценки РК в стратегии CPO, по умолчанию
     *
     * @param integer $strategyId
     *
     * @return array
     */
    public static function getDefaultHorizonStrategyCpo($strategyId)
    {
        $cpo = StrategyCpo::with('strategy')
            ->where('strategy_id', $strategyId)
            ->first();

        if ($cpo->strategy->strategy_status_id === StrategyStatus::ACTIVE) {
            $dateFrom = Carbon::now()->subDays($cpo->horizon + 1);
            $dateTo   = Carbon::now()->subDay();

            if ($dateFrom < $cpo->strategy->campaign->created_at) {
                $dateFrom = $cpo->strategy->campaign->created_at;
            }
        }else{
            $dateFrom = $cpo->created_at;
            $dateTo = StrategyHistory::where([
                    ['strategy_id', $cpo->strategy->id],
                    ['parameter', 'status_id'],
                    ['value_after', StrategyStatus::NOT_ACTIVE]
                ])
                ->orderBy('id', 'desc')
                ->first()
                ->created_at;
        }

        return [
            'from' => $dateFrom,
            'to'   => $dateTo
        ];
    }
}
