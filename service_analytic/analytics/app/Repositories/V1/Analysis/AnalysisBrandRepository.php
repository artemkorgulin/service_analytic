<?php

namespace App\Repositories\V1\Analysis;

use App\Helpers\StatisticQueries;
use App\Contracts\Repositories\V1\Analysis\AnalysisBrandRepositoryInterface;
use App\Models\Brand;
use App\Models\CardProduct;
use App\Models\Categories\CategoryVendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AnalysisBrandRepository implements AnalysisBrandRepositoryInterface
{
    public int $brand_id;

    /**
     * @param  int  $brandId
     * @return Brand|ModelNotFoundException
     */
    public function findByBrandId(int $brandId): Brand|ModelNotFoundException
    {
        return Brand::query()->where('brand_id', $brandId)->firstOrFail();
    }

    public function getAllBrandProducts(Brand $brand): array
    {
        return CardProduct::where('brand_id', $brand->id)->get()->toArray();
    }

    /**
     * @return int
     */
    public function getBrandId(): int
    {
        return $this->brand_id;
    }

    /**
     * Получить данные для ценового  анализа.
     * @param  int  $brandId
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  null  $selectBlock
     * @return mixed
     */
    public function getAnalysis(int $brandId, string $startDate, string $endDate, $selectBlock = null): mixed
    {
        $subQuery = CardProduct::selectRaw("   card_products.vendor_code,
                                               card_products.suppliers_id,
                                               category_vendor.subject_id,
                                               COUNT(DISTINCT card_products.id) AS products,
                                               COUNT(DISTINCT category_vendor.vendor_code) AS sku_count,
                                               COUNT(DISTINCT card_products.suppliers_id) AS suppliers_count")
            ->join('category_vendor', 'card_products.vendor_code', '=', 'category_vendor.vendor_code')
            ->join('product_info', function ($join) use ($brandId, $startDate, $endDate) {
                $join->on('card_products.vendor_code', '=', 'product_info.vendor_code')
                    ->where('card_products.brand_id', '=', $brandId)
                    ->where('product_info.date', '>', $startDate)
                    ->where('product_info.date', '<', $endDate);
            })
            ->groupBy('card_products.vendor_code',)
            ->groupBy('category_vendor.subject_id', 'card_products.suppliers_id');

        return  DB::query()->fromSub($subQuery, 'a')
            ->select(DB::raw($selectBlock))
            ->join(DB::raw(StatisticQueries::getAggregateProductInfo('a', $startDate, $endDate)),
                'method_aggregate_product_info.vendor_code', '=', 'a.vendor_code')
            ->leftJoin(DB::raw(StatisticQueries::getProductInfo('a', $endDate)),
                'method_product_info.vendor_code', '=', 'a.vendor_code');
    }
}
