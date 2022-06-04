<?php

namespace App\Helpers;

use App\Constants\Constants;
use App\Models\CampaignProductStatistic;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsHelper extends StatisticHelper
{
    /**
     * Сформировать запрос фильтрации
     *
     * @param $from
     * @param $to
     * @param $campaignIds
     * @param $campaignProductsIds
     * @param $statusesIds
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeFilterQuery(Request $request)
    {
        $accountId = UserService::getCurrentAccountId();
        return CampaignProductStatistic::query()
            ->join('campaign_products', 'campaign_product_statistics.campaign_product_id', '=', 'campaign_products.id')
            ->join('campaigns', 'campaign_products.campaign_id', '=', 'campaigns.id')
            ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
            ->join('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
            ->when($from = $request->from, function (Builder $query) use ($from) {
                $query->whereDate('campaign_product_statistics.date', '>=', $from);
            })
            ->when($to = $request->to, function (Builder $query) use ($to) {
                $query->whereDate('campaign_product_statistics.date', '<=', $to);
            })
            ->when($campaignIds = $request->campaigns, function (Builder $query) use ($campaignIds) {
                $query->whereIn('campaign_products.campaign_id', $campaignIds);
            })
            ->when($campaignProductsIds = $request->products, function (Builder $query) use ($campaignProductsIds) {
                $query->whereIn('campaign_products.id', $campaignProductsIds);
            })
            ->when($statusesIds = $request->statuses, function (Builder $query) use ($statusesIds) {
                $query->whereIn('campaigns.status_id', $statusesIds);
            })
                                    ->when($placementsIds = $request->placements, function(Builder $query) use ($placementsIds) {
                                        $query->whereIn('campaigns.placement_id', $placementsIds);
                                    })
                                    ->where('campaigns.account_id', $accountId)
                                    ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
                                    ->where('campaign_page_types.name', Constants::CAMPAIGN_SEARCH);
    }

    /**
     * Сформировать запрос детальной статистики
     *
     * @param Request $request
     *
     * @return Builder|mixed
     */
    public static function makeDetailedStatisticQuery(Request $request)
    {
        $query = self::makeFilterQuery($request);

        $detailedQuery = $query->selectRaw('DATE_FORMAT(campaign_product_statistics.date, "%d.%m.%Y") as date');

        return self::addCharacteristicsSelect($detailedQuery, 'campaign_product_statistics')
            ->orderBy('campaign_product_statistics.date', 'desc')
            ->groupBy('campaign_product_statistics.date');
    }

    /**
     * Сформировать запрос итогов статистики
     * @param  Request $request
     * @return Builder|mixed
     */
    public static function makeStatisticTotalsQuery(Request $request)
    {
        $query = self::makeFilterQuery($request);

        return self::addCharacteristicsSelect($query, 'campaign_product_statistics');
    }
}
