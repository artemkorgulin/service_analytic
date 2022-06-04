<?php

namespace App\Helpers;

use App\Models\CampaignProduct;
use App\Constants\Constants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class ProductStatisticHelper extends StatisticHelper
{
    /**
     * Сформировать запрос фильтрации
     *
     * @param $from
     * @param $to
     * @param $campaignsIds
     * @param $groupsIds
     * @param $productsIds
     * @param $statusesIds
     * @param $priceFrom
     * @param $priceTo
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeFilterQuery($from, $to, $campaignsIds, $groupsIds, $productsIds, $statusesIds, $priceFrom, $priceTo, $accountId)
    {
        return CampaignProduct::query()
            ->leftJoin('campaign_product_statistics', function (JoinClause $join) use ($from, $to) {
                $join->on('campaign_product_statistics.campaign_product_id', '=', 'campaign_products.id')
                    ->whereDate('campaign_product_statistics.date', '>=', $from)
                    ->whereDate('campaign_product_statistics.date', '<=', $to);
            })
            ->join('statuses', 'statuses.id', '=', 'campaign_products.status_id')
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->leftJoin('groups', 'groups.id', '=', 'campaign_products.group_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->when($campaignsIds, function (Builder $query) use ($campaignsIds) {
                $query->whereIn('campaign_products.campaign_id', $campaignsIds);
            })
            ->when($groupsIds, function (Builder $query) use ($groupsIds) {
                $query->whereIn('campaign_products.group_id', $groupsIds);
            })
            ->when($productsIds, function (Builder $query) use ($productsIds) {
                $query->whereIn('campaign_products.id', $productsIds);
            })
            ->when($statusesIds, function (Builder $query) use ($statusesIds) {
                $query->whereIn('campaign_products.status_id', $statusesIds);
            })
            ->when($priceFrom && $priceTo, function (Builder $query) use ($priceFrom, $priceTo) {
                $query->whereBetween('products.price', [$priceFrom, $priceTo]);
            })
            ->whereHas('campaign', function (Builder $query) use ($accountId) {
                $query
                    ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
                    ->join('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
                    ->where('account_id', $accountId)
                    ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
                    ->where('campaign_page_types.name', Constants::CAMPAIGN_SEARCH);
            });
    }

    /**
     * Сформировать запрос детальной статистики
     *
     * @param $from
     * @param $to
     * @param $campaignIds
     * @param $groupsIds
     * @param $productsIds
     * @param $statusesIds
     * @param $priceFrom
     * @param $priceTo
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeDetailedStatisticQuery($from, $to, $campaignIds, $groupsIds, $productsIds, $statusesIds, $priceFrom, $priceTo, $accountId)
    {
        $query = self::makeFilterQuery($from, $to, $campaignIds, $groupsIds, $productsIds, $statusesIds, $priceFrom, $priceTo, $accountId);

        if ($groupsIds) {
            $detailedQuery = $query
                ->select('campaign_products.id')
                ->addSelect('products.id as product_id')
                ->addSelect('products.name AS product_name')
                ->addSelect('products.sku AS sku')
                ->addSelect('campaign_products.status_id AS status_id')
                ->addSelect('statuses.name AS status_name')
                ->addSelect('products.category_id AS category_id')
                ->addSelect('categories.name AS category_name')
                ->addSelect('products.price AS price')
                ->addSelect('campaign_products.group_id')
                ->selectRaw('IF(groups.name IS NOT NULL, groups.name, groups.ozon_id) AS group_name');

            return self::addCharacteristicsSelect($detailedQuery, 'campaign_product_statistics')
                ->orderBy('campaign_products.group_id', 'desc')
                ->orderBy('campaign_products.id', 'asc')
                ->when($groupsIds, function (Builder $query) {
                    return $query->groupBy('campaign_products.product_id');
                }, function (Builder $query) {
                    return $query->groupBy(DB::raw('IFNULL(campaign_products.group_id, campaign_products.product_id)'));
                });
        }

        $detailedQuery = $query
            ->select('campaign_products.id')
            ->selectRaw('IF(campaign_products.group_id IS NULL, products.id, NULL) AS product_id')
            ->selectRaw('IF(campaign_products.group_id IS NULL, products.name, NULL) AS product_name')
            ->selectRaw('IF(campaign_products.group_id IS NULL, products.sku, NULL) AS sku')
            ->selectRaw('IF(campaign_products.group_id IS NULL, campaign_products.status_id, NULL) AS status_id')
            ->selectRaw('IF(campaign_products.group_id IS NULL, statuses.name, NULL) AS status_name')
            ->selectRaw('IF(campaign_products.group_id IS NULL, products.category_id, NULL) AS category_id')
            ->selectRaw('IF(campaign_products.group_id IS NULL, categories.name, NULL) AS category_name')
            ->selectRaw('IF(campaign_products.group_id IS NULL, products.price, NULL) AS price')
            ->addSelect('campaign_products.campaign_id')
            ->addSelect('campaign_products.group_id')
            ->selectRaw('IF(groups.name IS NOT NULL, groups.name, groups.ozon_id) AS group_name')
            ->selectRaw('COUNT(DISTINCT campaign_products.id) AS products_count');

        return self::addCharacteristicsSelect($detailedQuery, 'campaign_product_statistics')
            ->orderBy('campaign_products.group_id', 'desc')
            ->orderBy('campaign_products.id', 'asc')
            ->groupBy(DB::raw('IFNULL(campaign_products.group_id, campaign_products.product_id)'));
    }

    /**
     * Сформировать запрос итогов статистики
     *
     * @param $from
     * @param $to
     * @param $campaignIds
     * @param $groupsIds
     * @param $productsIds
     * @param $statusesIds
     * @param $priceFrom
     * @param $priceTo
     * @param $accountId
     *
     * @return Builder|mixed
     */
    public static function makeStatisticTotalsQuery($from, $to, $campaignIds, $groupsIds, $productsIds, $statusesIds, $priceFrom, $priceTo, $accountId)
    {
        $query = self::makeFilterQuery($from, $to, $campaignIds, $groupsIds, $productsIds, $statusesIds, $priceFrom, $priceTo, $accountId);

        return self::addCharacteristicsSelect($query, 'campaign_product_statistics');
    }
}
