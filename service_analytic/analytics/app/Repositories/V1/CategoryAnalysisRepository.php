<?php

namespace App\Repositories\V1;

use App\Helpers\StatisticQueries;
use App\Models\Categories\CategoryVendor;
use Illuminate\Support\Facades\DB;

class CategoryAnalysisRepository
{
    /**
     * Получить данные для категорийного анализа.
     *
     * @param $brands
     * @param $startDate
     * @param $endDate
     * @param $selectBlock
     * @return mixed
     */
    public function getAnalysis($brands, $startDate, $endDate, $selectBlock)
    {
        $subQuery = CategoryVendor::selectRaw('card_products.brand_id,
                                               category_vendor.subject_id,
                                               MAX(category_vendor.web_id) as web_id,
                                               COUNT(DISTINCT category_vendor.vendor_code) AS sku_count,
                                               COALESCE(SUM(method_aggregate_product_info_for_brands.revenue), 0) AS take,
                                               COUNT(DISTINCT card_products.suppliers_id) AS suppliers_count')
            ->join('card_products', function ($join) use ($brands, $endDate) {
                $join->on('card_products.vendor_code', '=', 'category_vendor.vendor_code')
                    ->whereIn('card_products.brand_id', $brands)
                    ->where('category_vendor.created_at', '<', $endDate);
            }
            )
            ->join(DB::raw(StatisticQueries::getAggregateProductInfoForBrands('card_products', $startDate, $endDate)),
                'method_aggregate_product_info_for_brands.vendor_code', '=', 'card_products.vendor_code')
            ->groupBy('card_products.brand_id')
            ->groupBy('category_vendor.subject_id');

        $result = DB::query()->fromSub($subQuery, 'a')
            ->select(DB::raw($selectBlock))
            ->join(DB::raw(StatisticQueries::getBreadcrumbsCategory('a')),
                'a.web_id', '=', 'method_breadcrumbs_category.web_id');

        return $result;
    }

    /**
     * Получить средние данные по бренду для категорийного анализа.
     *
     * @param $brands
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getAvgAnalysis($brands, $startDate, $endDate)
    {
        return CategoryVendor::selectRaw('card_products.brand_id,
                                          COUNT(DISTINCT category_vendor.vendor_code) AS sku_count,
                                          COUNT(DISTINCT card_products.suppliers_id) AS suppliers_count')
            ->join('card_products', function ($join) use ($brands, $endDate) {
                $join->on('card_products.vendor_code', '=', 'category_vendor.vendor_code')
                    ->whereIn('card_products.brand_id', $brands)
                    ->where('category_vendor.created_at', '<', $endDate);
            }
            )
            ->groupBy('card_products.brand_id');
    }
}
