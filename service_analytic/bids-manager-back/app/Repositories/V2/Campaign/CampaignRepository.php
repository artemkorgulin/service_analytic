<?php

namespace App\Repositories\V2\Campaign;

use App\Contracts\Repositories\CampaignRepositoryInterface;
use App\Http\Requests\V2\Campaign\CampaignsFilterRequest;
use App\Http\Requests\V2\Campaign\CampaignStatisticRequest;
use App\Models\Campaign;
use App\Models\CampaignPageType;
use App\Models\CampaignPaymentType;
use App\Models\CampaignPlacement;
use App\Models\CampaignStatistic;
use App\Models\CampaignStatus;
use App\Models\CampaignType;
use App\Models\StrategyStatus;
use App\Models\StrategyType;
use App\Repositories\V2\Product\ProductRepository;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Constants\DateTimeConstants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function allByFilters(CampaignsFilterRequest $request): Builder
    {
        $request = $request->all();
        $campaignData = Campaign::with([
            'paymentType',
            'strategy',
            'campaignStatus',
            'placement',
            'campaignType',
            'sumStatistics',
            'strategyShowCounts',
            'strategyCpoCounts'
        ])->where([['user_id', '=', UserService::getUserId()], ['account_id', '=', UserService::getCurrentAccountId()]]);

        if (!empty($request['search'])) {
            return $campaignData->where('campaigns.name', 'like', '%' . $request['search'] .'%');
        }

        if (!empty($request['campaign_ids'])) {
            $campaignData->whereIn('campaigns.id', $request['campaign_ids']);
        }

        if (!empty($request['campaign_payment_type_id'])) {
            $campaignData->whereIn('payment_type_id', $request['campaign_payment_type_id']);
        }

        if (!empty($request['campaign_status_ids'])) {
            $campaignData->whereIn('campaign_status_id', $request['campaign_status_ids']);
        }

        if (!empty($request['campaign_type_ids'])) {
            $campaignData->whereIn('type_id', $request['campaign_type_ids']);
        }

        if (!empty($request['campaign_placement_id'])) {
            $campaignData->whereIn('placement_id', $request['campaign_placement_id']);
        }

        if (!empty($request['campaign_page_type_id'])) {
            $campaignData->whereIn('page_type_id', $request['campaign_page_type_id']);
        }

        if (array_key_exists('campaign_budget_start', $request)) {
            $campaignData->where('budget', '>=', $request['campaign_budget_start']);
        }

        if (array_key_exists('campaign_budget_end', $request)) {
            $campaignData->where('budget', '<=', $request['campaign_budget_end']);
        }

        if (!empty($request['campaign_strategy_type_id'])) {
            $campaignData->whereHas('strategy', function ($query) use ($request) {
                return $query->whereIn('strategy_type_id', $request['campaign_strategy_type_id']);
            });
        }

        return $campaignData;
    }

    public function getCampaignFilterOptions(): array
    {
        $cacheTime = DateTimeConstants::COUNT_SECONDS_IN_DAY;
        $campaignStatuses = Cache::remember('campaign_statuses', $cacheTime, function () {
            return CampaignStatus::all();
        });
        $campaignTypes = Cache::remember('campaign_types', $cacheTime, function () {
            return CampaignType::all();
        });
        $strategyTypes = Cache::remember('strategy_types', $cacheTime, function () {
            return StrategyType::all(['id', 'name']);
        });
        $strategyStatuses = Cache::remember('strategy_statuses', $cacheTime, function () {
            return StrategyStatus::all();
        });
        $campaignPlacements = Cache::remember('campaign_placements', $cacheTime, function () {
            return CampaignPlacement::all();
        });
        $campaignPaymentType = Cache::remember('campaign_payment_types', $cacheTime, function () {
            return CampaignPaymentType::all();
        });
        $campaignPageTypes = Cache::remember('campaign_page_types', $cacheTime, function () {
            return CampaignPageType::all();
        });

        return [
            'campaign_statuses' => $campaignStatuses,
            'strategy_types' => $strategyTypes,
            'campaign_types' => $campaignTypes,
            'strategy_statuses' => $strategyStatuses,
            'campaign_placements' => $campaignPlacements,
            'campaign_payment_types' => $campaignPaymentType,
            'campaign_page_types' => $campaignPageTypes
        ];
    }

    public function getProducts(Campaign $campaign)
    {
        $ids = $campaign->campaignProducts()->pluck('product_id')->toArray();
        return (new ProductRepository())->getProducts($ids, UserService::getUserId());
    }

    public function getStatistic(CampaignStatisticRequest $request): array
    {
        $query = CampaignStatistic::query()
            ->leftJoin('campaigns', 'campaign_statistics.campaign_id', '=', 'campaigns.id')
            ->where(['campaigns.user_id' => UserService::getUserId(), 'campaigns.account_id' => UserService::getCurrentAccountId()]);

        if (!empty($request->campaign_ids)) {
            $query->whereIn('campaign_id', $request->campaign_ids);
        }

        if (!empty($request->start_date)) {
            $query->where('campaign_statistics.date', '>=', $request->start_date);
        }

        if (!empty($request->end_date)) {
            $query->where('campaign_statistics.date', '<=', $request->end_date);
        }

        $totalStatistics = Campaign::addStatisticsSumColumns($query, 'campaign_statistics.date')->get();

        return is_object($totalStatistics) && method_exists($totalStatistics, 'toArray')
            ? $totalStatistics->toArray()
            : [
                'popularity' => null,
                'shows' => null,
                'clicks' => null,
                'cost' => null,
                'orders' => null,
                'profit' => null,
                'drr' => null,
                'cpo' => null,
                'acos' => null,
                'purchased_shows' => null,
                'ctr' => null,
                'avg_1000_shows_price' => null,
                'avg_click_price' => null
            ];
    }

    public function getTotalStatistic($campaignIds = []): array
    {
        $query = CampaignStatistic::query()
            ->leftJoin('campaigns', 'campaign_statistics.campaign_id', '=', 'campaigns.id')
            ->where(['campaigns.user_id' => UserService::getUserId(), 'campaigns.account_id' => UserService::getCurrentAccountId()]);

        $totalStatistics = Campaign::addStatisticsSumColumns($query);

        if (!empty($campaignIds) && is_array($campaignIds)) {
            $totalStatistics->whereIn('campaign_id', $campaignIds);
        }

        $totalStatistics = $totalStatistics->first();

        return is_object($totalStatistics) && method_exists($totalStatistics, 'toArray')
            ? $totalStatistics->toArray()
            : [
                'popularity' => null,
                'shows' => null,
                'clicks' => null,
                'cost' => null,
                'orders' => null,
                'profit' => null,
                'drr' => null,
                'cpo' => null,
                'acos' => null,
                'purchased_shows' => null,
                'ctr' => null,
                'avg_1000_shows_price' => null,
                'avg_click_price' => null
            ];
    }
}
