<?php

namespace App\Helpers;

use App\Models\CampaignKeyword;
use App\Constants\Constants;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class KeywordStatisticHelper extends StatisticHelper
{
    /**
     * Сформировать запрос фильтрации
     *
     * @param $from
     * @param $to
     * @param $campaignsIds
     * @param $campaignProductsIds
     * @param $statusesIds
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeFilterQuery($from, $to, $campaignsIds, $campaignProductsIds, $statusesIds, $accountId)
    {
        return CampaignKeyword::query()
            ->leftJoin('campaign_keyword_statistics', function (JoinClause $join) use ($from, $to) {
                $join->on('campaign_keyword_statistics.campaign_keyword_id', '=', 'campaign_keywords.id')
                    ->whereDate('campaign_keyword_statistics.date', '>=', $from)
                    ->whereDate('campaign_keyword_statistics.date', '<=', $to);
            })
            ->join('campaign_products', 'campaign_products.id', '=', 'campaign_keywords.campaign_product_id')
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->leftJoin('groups', 'groups.id', '=', 'campaign_keywords.group_id')
            ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
            ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
            ->join('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
            ->leftJoin('strategies', 'campaigns.id', '=', 'strategies.campaign_id')
            ->join('keywords', 'keywords.id', '=', 'campaign_keywords.keyword_id')
            ->join('statuses', 'statuses.id', '=', 'campaign_keywords.status_id')
            ->when($statusesIds, function (Builder $query) use ($statusesIds) {
                $query->whereIn('campaign_keywords.status_id', $statusesIds);
            })
            ->when($campaignProductsIds, function (Builder $query) use ($campaignProductsIds) {
                $query->whereIn('campaign_products.id', $campaignProductsIds);
            })
                              ->when($campaignsIds, function(Builder $query) use ($campaignsIds) {
                                  $query->whereIn('campaigns.id', $campaignsIds);
                              })
                              ->where('campaigns.account_id', $accountId)
                              ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
                              ->where('campaign_page_types.name', Constants::CAMPAIGN_SEARCH);
    }

    /**
     * Сформировать запрос детальной статистики
     *
     * @param $from
     * @param $to
     * @param $campaignsIds
     * @param $campaignProductsIds
     * @param $statusesIds
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeDetailedStatisticQuery($from, $to, $campaignsIds, $campaignProductsIds, $statusesIds, $accountId)
    {
        $query = self::makeFilterQuery($from, $to, $campaignsIds, $campaignProductsIds, $statusesIds, $accountId);

        $detailedQuery = $query
            ->select(
                'campaign_keywords.id',
                'keywords.id as keyword_id',
                'keywords.name AS keyword_name',
                'products.sku',
                'products.price',
                'campaign_keywords.status_id as status_id',
                'statuses.name AS status_name',
                'strategies.id as strategy_id',
                'campaign_keywords.bid as bid',
            )
                            ->selectRaw("(CASE WHEN keywords.name IS NULL THEN sku ELSE keywords.name END) AS keyword");

        return self::addCharacteristicsSelect($detailedQuery, 'campaign_keyword_statistics')
            ->orderBy('products.sku', 'asc')
                   ->orderBy('keywords.id', 'asc')
                   ->groupBy('campaign_keywords.id');
    }

    /**
     * Сформировать запрос итогов статистики
     *
     * @param $from
     * @param $to
     * @param $campaignsIds
     * @param $campaignProductsIds
     * @param $statusesIds
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeStatisticTotalsQuery($from, $to, $campaignsIds, $campaignProductsIds, $statusesIds, $accountId)
    {
        $query = self::makeFilterQuery($from, $to, $campaignsIds, $campaignProductsIds, $statusesIds, $accountId);

        return self::addCharacteristicsSelect($query, 'campaign_keyword_statistics');
    }
}
