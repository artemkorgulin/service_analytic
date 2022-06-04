<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Campaign;
use App\Models\CampaignKeyword;
use App\Models\CampaignStatus;
use App\Constants\Constants;
use App\Helpers\StrategyHelper;
use App\Http\Controllers\Controller;
use App\Models\Strategy;
use App\Models\StrategyCpo;
use App\Models\StrategyStatus;
use App\Models\StrategyType;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StrategyController extends Controller
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_COUNT = 15;

    /***************************/
    /* Управление ставками 2.0 */
    /***************************/

    /**
     * Список стратегий (типов)
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getStrategiesList(Request $request)
    {
        $request->validate(
            [
                'per_page' => 'integer',
                'page'     => 'integer'
            ]
        );

        $currentPage = $request->get('page') ?? static::DEFAULT_PAGE;
        $perPage = $request->get('per_page') ?? static::DEFAULT_COUNT;
        $strategiesTypes = StrategyType::query()
            ->withCount([
                'strategies as strategies_all' => function($query) {
                   $query->whereHas('campaign', function (Builder $query) {
                       $query->where('account_id', UserService::getCurrentAccount());
                   });
                },
                'strategies as strategies_active' => function($query) {
                    $query->where('strategy_status_id', StrategyStatus::ACTIVE)
                        ->whereHas('campaign', function (Builder $query) {
                            $query->where('account_id', UserService::getCurrentAccount());
                        });
                }
            ])->paginate($perPage,  ['*'], 'page', $currentPage);

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'strategies' => $strategiesTypes
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Список рекламных кампаний стратегии
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getStrategyCampaignsList(Request $request)
    {
        $request->validate(
            [
                'strategyTypeId' => 'required|integer',
                'sortBy'         => 'string',
                'sortDir'        => 'string',
                'per_page'       => 'integer',
                'page'           => 'integer'
            ]
        );

        $strategyTypeId = $request->get('strategyTypeId');

        $sortBy  = $request->get('sortBy')  ?? 'strategy_status_id';
        $sortDir = $request->get('sortDir') ?? 'asc';

        $currentPage = $request->get('page') ?? static::DEFAULT_PAGE;
        $perPage = $request->get('per_page') ?? static::DEFAULT_COUNT;

        $strategyType = StrategyType::find($strategyTypeId);

        $campaigns = StrategyHelper::makeStrategyCampaignsQuery($strategyTypeId, $sortBy, $sortDir)
                                   ->paginate($perPage,  ['*'], 'page', $currentPage);

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'strategyId' => $strategyType->id,
                    'strategyName' => $strategyType->name,
                    'strategyBehavior' => $strategyType->behavior,
                    'campaigns' => $campaigns,
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * История стратегии рекламной кампании
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getStrategyCampaignHistory(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required|integer',
                'from'       => 'date',
                'to'         => 'date',
                'group'      => 'string',
                'keywordId'  => 'array',
                'per_page'   => 'integer',
                'page'       => 'integer',
                'all'        => 'boolean',
                'order'      => 'string',
            ]
        );

        $from = $request->get('from');
        $to   = $request->get('to');

        $strategyId = $request->get('strategyId');
        $group      = $request->get('group');
        $order      = $request->get('order') ?? 'desc';

        $keywordIds = $request->get('keywordId');

        $currentPage = $request->get('page') ?? static::DEFAULT_PAGE;
        $perPage = $request->get('per_page') ?? static::DEFAULT_COUNT;
        $all = $request->get('all');

        $needReformat = $request->needReformat ?? true;

        $strategy = Strategy::find($strategyId);

        if( !$strategy ) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => [__('front.strategy_not_found')],
                ]
            );
        }

        if( $strategy->campaign->account_id != UserService::getCurrentAccount() ) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'errors' => [__('front.campaign_not_allowed_this_account')],
                ]
            );
        }

        $keywordsData = StrategyHelper::getStrategyKeywordsHistory($strategy, $needReformat, $group, $keywordIds, $from, $to, [
            'all'          => $all,
            'per_page'     => $perPage,
            'current_page' => $currentPage,
            'order'        => $order
        ]);

        return response()->json(
            [
                'success' => true,
                'errors'  => [],
                'data' => array_merge(StrategyHelper::getDataResponse($strategy), $keywordsData)
            ]
        );
    }

    /**
     * Список РК, которые еще не входят в стратегию
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCampaignsListForStrategy(Request $request)
    {
        $request->validate(
            [
                'strategyTypeId' => 'required|integer',
            ]
        );

        $strategyTypeId = $request->get('strategyTypeId');

        $accountId = UserService::getCurrentAccount();

        $existedStrategyCampaignsIds = Strategy::query()
                                               ->where('strategy_type_id', $strategyTypeId)
                                               ->pluck('campaign_id');

        $campaigns = Campaign::query()
                             ->join('campaign_statuses', 'campaign_statuses.id', '=', 'campaigns.campaign_status_id')
                             ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
                             ->join('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
                             ->whereNotIn('campaigns.id', $existedStrategyCampaignsIds)
                             ->where('campaigns.account_id', UserService::getCurrentAccount())
                             ->whereIn('campaign_statuses.code', [CampaignStatus::ACTIVE])
                             ->where('campaigns.account_id', $accountId)
                             ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
                             ->where('campaign_page_types.name', Constants::CAMPAIGN_SEARCH)
                             ->select('campaigns.id', 'campaigns.name')
                             ->orderBy('campaigns.id', 'desc')
                             ->take(Constants::TAKE_COUNT);

        if ($strategyTypeId == StrategyType::OPTIMIZE_CPO) {
            $checkDate = Carbon::now()->subDays(StrategyCpo::DAYS_IN_HORIZON);
            $campaigns = $campaigns->withCount([
                    'statistics as orders_count' => function ($query) use ($checkDate) {
                        $query->where('campaign_statistics.created_at', '>=', $checkDate)
                            ->select(DB::raw('SUM(orders) as orders'));
                    }
                ])
                ->get()
                ->where('orders_count', '>', StrategyCpo::MIN_ORDERS);
        }else{
            $campaigns = $campaigns->get();
        }

        return response()->json(
            [
                'success' => true,
                'data' => $campaigns,
                'errors' => [],
            ]
        );
    }

    /**
     * Список ключевиков для фильтра в стратегии
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getKeywordsListForStrategy(Request $request)
    {
        $request->validate(
            [
                'strategyId'   => 'required|integer',
                'group'        => 'string',
                'keywordTitle' => 'string',
            ]
        );

        $strategyId = $request->get('strategyId');
        $strategy   = Strategy::find($strategyId);

        $group = $request->get('group');
        $keywordTitle = strtolower($request->get('keywordTitle'));

        switch( $strategy->strategy_type_id ) {
            case StrategyType::OPTIMIZE_CPO:
                $dateRange = StrategyHelper::getDefaultHorizonStrategyCpo($strategyId);
                break;
            default:
                $dateRange = [
                    'from' => Carbon::now()->subDays(8),
                    'to'   => Carbon::now()->subDays(1)
                ];
        }

        $accountId = UserService::getCurrentAccount();

        $keywords = CampaignKeyword::query()
            ->join('keywords', 'keywords.id', '=', 'campaign_keywords.keyword_id')
            ->join('campaign_products', 'campaign_products.id', '=', 'campaign_keywords.campaign_product_id')
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->leftJoin('groups', 'groups.id', '=', 'campaign_keywords.group_id')
            ->when($keywordTitle, function (Builder $query) use ($keywordTitle) {
                $query->where('keywords.name', 'like', '%' . $keywordTitle . '%');
            })
            ->when($group, function (Builder $query) use ($group) {
                if ((int)$group > 0 && (int)$group == $group) {
                    $query->where('campaign_keywords.group_id', $group);
                } elseif ($group === 'without') {
                    $query->whereNull('campaign_keywords.group_id');
                }
            })
            ->whereHas('campaignProduct', function (Builder $query) use ($accountId, $strategyId) {
                $query->whereHas('campaign', function (Builder $query) use ($accountId, $strategyId) {
                    $query->where('account_id', $accountId);
                    $query->whereHas('strategy', function (Builder $query) use ($strategyId) {
                        $query->where('id', $strategyId);
                    });
                });
            })
            ->join('campaign_keyword_statistics', 'campaign_keyword_statistics.campaign_keyword_id', '=', 'campaign_keywords.id')
            ->when($dateRange, function(Builder $query) use ($dateRange) {
                $delta = $dateRange['from']->diff($dateRange['to']);

                if ($delta->days) {
                    $query->where([
                        ['campaign_keyword_statistics.date', '>=', $dateRange['from']],
                        ['campaign_keyword_statistics.date', '<=', $dateRange['to']]
                    ]);
                } else {
                    $query->whereDate('campaign_keyword_statistics.date', $dateRange['to']);
                }
            })
            ->orderBy('products.sku', 'asc')
            ->orderBy('keywords.name', 'asc')
            ->select(
                'campaign_keywords.id',
                'keywords.name',
                'products.sku',
            )
            ->groupBy('campaign_keywords.id')
            ->selectRaw('IF(groups.name IS NOT NULL, groups.name, groups.ozon_id) AS group_name')
            ->take(Constants::TAKE_COUNT)
            ->get();

        return response()->json(
            [
                'success' => true,
                'data' => $keywords,
                'errors' => [],
            ]
        );
    }

    /**
     * Кол-во компаний доступных для применения в стратегии CPO
     *
     * @return JsonResponse
     */
    public function getCountCompanyForStrategyCpo()
    {
        $checkDate = Carbon::now()->subDays(StrategyCpo::DAYS_IN_HORIZON);
        $counter   = Campaign::query()
                            ->join('campaign_statuses', 'campaign_statuses.id', '=', 'campaigns.campaign_status_id')
                            ->where('campaigns.account_id', UserService::getCurrentAccountId())
                            ->whereIn('campaign_statuses.code', [CampaignStatus::ACTIVE])
                            ->withCount([
                                'statistics as orders_count' => function ($query) use ($checkDate) {
                                    $query->where('campaign_statistics.created_at', '>=', $checkDate)
                                          ->select(DB::raw('SUM(orders) as orders'));
                                }
                            ])
                            ->get()
                            ->where('orders_count', '>', StrategyCpo::MIN_ORDERS)
                              /*
                               * TODO   ограничение на мин кол-во
                               */
                            ->count();

        return response()->json(
            [
                'success' => true,
                'errors'  => [],
                'data'   => $counter
            ]
        );
    }

    /**
     * Получить горизонт оценки РК в стратегии CPO, по умолчанию
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDefaultHorizonStrategyCpo(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required|integer',
            ]
        );
        $strategyId = $request->get('strategyId');

        $strategy = Strategy::find($strategyId);

        if( !$strategy ) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => [__('front.strategy_not_found')],
                ]
            );
        }

        if( $strategy->campaign->account_id != UserService::getCurrentAccount() ) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'errors' => [__('front.campaign_not_allowed_this_account')],
                ]
            );
        }

        switch( $strategy->strategy_type_id ) {
            case StrategyType::OPTIMIZE_CPO:
                $dateRange = StrategyHelper::getDefaultHorizonStrategyCpo($strategyId);
                break;
            default:
                $dateRange = [
                    'from' => Carbon::now()->subDays(8),
                    'to'   => Carbon::now()->subDays(1)
                ];
        }

        return response()->json(
            [
                'success' => true,
                'data'    => $dateRange,
                'errors'  => [],
            ]
        );
    }
}
