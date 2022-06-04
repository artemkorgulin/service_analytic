<?php

namespace App\Repositories\V1;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\StatisticQueries;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BrandStatisticRepository
{
    protected $brand_id;
    protected $startDate;
    protected $endDate;

    private Collection|null $skuWbProducts;

    /**
     * @param array $requestParams
     */
    public function __construct(array $requestParams)
    {
        $skuWbProducts = null;
        if (isset($requestParams['my_products']) && $requestParams['my_products']) {
            $userId = $requestParams['user']['id'];
            $skuWbProducts = QueryBuilderHelper::getUserProducts($userId);
        }

        $this->brand_id = $requestParams['brandId'];
        $this->startDate = $requestParams['startDate'];
        $this->endDate = $requestParams['endDate'];
        $this->skuWbProducts = $skuWbProducts;
    }

    /**
     * @param  string  $selectBlock
     * @return mixed
     */
    public function getProducts(string $selectBlock)
    {
        $brand_id = $this->brand_id;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $result = DB::table('card_products')
            ->select(DB::raw($selectBlock))
            ->where('card_products.brand_id', '=', $brand_id)
            ->join(DB::raw(StatisticQueries::getProductCategoriesWithoutDublicate('card_products', $startDate,
                $endDate)),
                'method_product_categories_without_dublicate.vendor_code', '=', 'card_products.vendor_code')
            ->leftJoin('brands', 'brands.brand_id', '=', 'card_products.brand_id')
            ->leftJoin('suppliers', 'suppliers.supplier_id', '=', 'card_products.suppliers_id')
            ->join(DB::raw(StatisticQueries::getAggregateProductInfo('card_products', $startDate, $endDate)),
                'method_aggregate_product_info.vendor_code', '=', 'card_products.vendor_code')
            ->leftJoin(DB::raw(StatisticQueries::getProductInfo('card_products', $endDate)),
                'method_product_info.vendor_code', '=', 'card_products.vendor_code')
            ->leftJoin(DB::raw(StatisticQueries::getStock('card_products', $endDate)),
                'method_stock.vendor_code', '=', 'card_products.vendor_code')
            ->join(DB::raw(StatisticQueries::getBreadcrumbsCategory('method_product_categories_without_dublicate')),
                'method_product_categories_without_dublicate.web_id', '=', 'method_breadcrumbs_category.web_id');

        if ($this->skuWbProducts) {
            $result->whereIn('method_product_categories_without_dublicate.vendor_code', $this->skuWbProducts);
        }

        return $result;
    }

    public function getCategories($selectBlock)
    {
        $brand_id = $this->brand_id;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $categoryVendors = DB::table('category_vendor')
            ->select(DB::raw("
                                        category_vendor.web_id,
                                        CASE
                                            WHEN ct.subjects IS NULL THEN NULL
                                            ELSE category_vendor.subject_id
                                        END AS subject_id,
                                        AVG(COALESCE(method_aggregate_product_info_for_brands.grade, cp.grade, 0)) AS rating,
                                        SUM(COALESCE(method_aggregate_product_info_for_brands.comments_count, cp.comments, 0)) AS comments,
                                        COUNT(DISTINCT cp.vendor_code) AS products,
                                        COUNT(DISTINCT cp.vendor_code) filter (where method_aggregate_product_info_for_brands.total_sales > 0) AS products_with_sale,
                                        SUM(method_aggregate_product_info_for_brands.total_sales) AS sales,
                                        AVG(COALESCE(method_aggregate_product_info_for_brands.price_u/100::int, 0)) AS avg_price,
                                        SUM(ceiling(method_aggregate_product_info_for_brands.revenue/100)::int) AS take,
                                        COUNT(DISTINCT cp.suppliers_id) AS count_suppliers,
                                        COUNT(DISTINCT cp.suppliers_id) filter (where method_aggregate_product_info_for_brands.total_sales > 0) AS count_suppliers_with_sale
                                        "))
            ->join('card_products AS cp', function ($join) use ($brand_id, $endDate) {
                $join->on('category_vendor.vendor_code', '=', 'cp.vendor_code')
                    ->where('cp.brand_id', '=', $brand_id)
                    ->where('category_vendor.created_at', '<', $endDate);
            })
            ->join(DB::raw(StatisticQueries::getAggregateProductInfoForBrands('cp', $startDate, $endDate)),
                'method_aggregate_product_info_for_brands.vendor_code', '=', 'cp.vendor_code')
            ->join('category_trees AS ct', 'category_vendor.web_id', '=', 'ct.web_id');

        if ($this->skuWbProducts) {
            $categoryVendors->whereIn('category_vendor.vendor_code', $this->skuWbProducts);
        }

        $categoryVendors->groupBy('category_vendor.web_id')
            ->groupByRaw('
                                CASE
                                    WHEN ct.subjects IS NULL THEN NULL
                                    ELSE category_vendor.subject_id
                                END'
            );

        $result = DB::query()->fromSub($categoryVendors, 'a')
            ->select(DB::raw($selectBlock))
            ->join(DB::raw(StatisticQueries::getBreadcrumbsCategory('a')),
                'a.web_id', '=', 'method_breadcrumbs_category.web_id');

        return $result;
    }

    public function getSellers($selectBlock)
    {
        $brand_id = $this->brand_id;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $categoryVendors = DB::table('card_products AS cp')
            ->select(DB::raw("cp.suppliers_id,
                                        AVG(COALESCE(method_aggregate_product_info_for_brands.grade, cp.grade, 0)) AS rating,
                                        SUM(COALESCE(method_aggregate_product_info_for_brands.comments_count, cp.comments, 0)) AS comments,
                                        SUM(method_aggregate_product_info_for_brands.total_sales) AS sales,
                                        SUM(ceiling(method_aggregate_product_info_for_brands.revenue/100)::int) AS take,
                                        AVG(COALESCE(method_aggregate_product_info_for_brands.price_u/100::int, 0)) AS avg_price,
                                        COUNT(DISTINCT cp.vendor_code) AS products,
                                        COUNT(DISTINCT cp.vendor_code) filter (where method_aggregate_product_info_for_brands.total_sales > 0) AS products_with_sale
                                        "))
            ->where('cp.brand_id', $brand_id)
            ->where('cp.created_at', '<', $endDate)
            ->join(DB::raw(StatisticQueries::getAggregateProductInfoForBrands('cp', $startDate, $endDate)),
                'method_aggregate_product_info_for_brands.vendor_code', '=', 'cp.vendor_code');

        if ($this->skuWbProducts) {
            $categoryVendors->whereIn('cp.vendor_code', $this->skuWbProducts);
        }

        $categoryVendors->groupBy('cp.suppliers_id');

        $result = DB::query()->fromSub($categoryVendors, 'a')
            ->select(DB::raw($selectBlock))
            ->join('suppliers', 'suppliers.supplier_id', '=', 'a.suppliers_id');

        return $result;
    }
}
