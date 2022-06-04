<?php

namespace App\Repositories\V1;

use App\Contracts\Repositories\V1\DashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use \Illuminate\Database\Query\Builder;

class DashboardRepository implements DashboardRepositoryInterface
{
    private ?string $dateFrom;
    private ?string $dateTo;

    /**
     * @param array $vendorCodes
     * @param string|null $dateFrom
     * @param string|null $dateTo
     */
    public function __construct(
        private array $vendorCodes,
        ?string       $dateFrom = null,
        ?string       $dateTo = null
    ) {
        $this->dateFrom = $dateFrom ?? now()->subDays(self::DEFAULT_DATE_OFFSET)->format('Y-m-d');
        $this->dateTo = $dateTo ?? now()->format('Y-m-d');
    }

    /**
     * @param string $tableName
     * @param string|null $as
     * @param $vendorCodes
     * @return Builder
     */
    public function initQueryFilter(
        string  $tableName,
        ?string $as = null,
                $vendorCodes = null,
        string  $sort = 'percent'
    ): Builder {
        $dateFilter = 'date';
        $vendorCodeFilter = 'vendor_code';

        if ($as) {
            $dateFilter = $as . '.' . $dateFilter;
            $vendorCodeFilter = $as . '.' . $vendorCodeFilter;
        }

        return DB::table($tableName, $as)
            ->whereBetween($dateFilter, [$this->dateFrom, $this->dateTo])
            ->whereIn($vendorCodeFilter, $vendorCodes ?? $this->vendorCodes)
            ->orderBy($sort, 'desc');
    }

    /**
     * @return string
     */
    public function getDateFilterTemplate()
    {
        return sprintf('\'%s\' and \'%s\'', $this->dateFrom, $this->dateTo);
    }

    /**
     * @inerhitDoc
     * @see DashboardRepositoryInterface::productRevenuePercent()
     */
    public function productRevenuePercent(): array
    {
        $vendorCodes = $this->getVendorCodesFromGroup();

        $segment = $this->initQueryFilter('product_info', null, $vendorCodes, 'revenue')
            ->select(
                'vendor_code as vendor_codes',
                DB::raw('sum(revenue) as revenue')
            )
            ->groupBy('vendor_code')
            ->get();

        return $this->calculateGroupPercentSegmentation($segment, 'revenue');
    }

    /**
     * @inerhitDoc
     * @see DashboardRepositoryInterface::categoryRevenuePercent()
     */
    public function categoryRevenuePercent(): array
    {
        $sections = $this->initQueryFilter('product_info', 'product')
            ->select('cat.subject_id as category',
                DB::raw('sum(revenue) as revenue'),
                DB::raw("string_agg(distinct product.vendor_code::text, ',') as vendor_codes"),
                DB::raw('coalesce(round(sum(revenue) /
                    NULLIF(round((select sum(revenue)
                     from product_info as prod
                     where prod.date between ' . $this->getDateFilterTemplate() . '
                     and prod.vendor_code in (' . implode(',',
                        $this->vendorCodes) . ')), 2), 0) * 100, 2), 0) as percent')
            )
            ->crossJoin(DB::raw('lateral (
                          select
                            cat.vendor_code, cat.subject_id
                          from
                            category_vendor as cat
                          where cat.vendor_code = product.vendor_code
                          order by cat.date desc
                          limit 1
                        ) as "cat"'
            ))
            ->groupBy('cat.subject_id')
            ->get();

        return $this->calculatePercentSegmentation($sections);
    }

    /**
     * @inerhitDoc
     * @see DashboardRepositoryInterface::brandRevenuePercent()
     */
    public function brandRevenuePercent(): array
    {
        $segment = $this->initQueryFilter('product_info', 'product')
            ->select('brand.brand_id as brand',
                DB::raw('sum(revenue) as revenue'),
                DB::raw("string_agg(distinct product.vendor_code::text, ',') as vendor_codes"),
                DB::raw('coalesce(round(sum(revenue) /
                    NULLIF(round((select sum(revenue)
                     from product_info as prod
                     where prod.date between ' . $this->getDateFilterTemplate() . '
                     and prod.vendor_code in (' . implode(',',
                        $this->vendorCodes) . ')), 2), 0) * 100, 2), 0) as percent')
            )
            ->crossJoin(DB::raw('lateral (
                          select
                            brand.vendor_code, brand.brand_id
                          from
                            card_products as brand
                          where brand.vendor_code = product.vendor_code
                        ) as "brand"'
            ))
            ->groupBy('brand.brand_id')
            ->get();

        return $this->calculatePercentSegmentation($segment);
    }

    /**
     * @inerhitDoc
     * @see DashboardRepositoryInterface::productOrderedPercent()
     */
    public function productOrderedPercent(): array
    {
        $vendorCodes = $this->getVendorCodesFromGroup();

        $segment = $this->initQueryFilter('product_info', null, $vendorCodes, 'current_sales')
            ->select(
                'vendor_code as vendor_codes',
                DB::raw('sum(current_sales) as current_sales'),
            )
            ->groupBy('vendor_code')
            ->get();

        return $this->calculateGroupPercentSegmentation($segment, 'current_sales');
    }

    /**
     * @inerhitDoc
     * @see DashboardRepositoryInterface::categoryOrderedPercent()
     */
    public function categoryOrderedPercent(): array
    {
        $segment = $this->initQueryFilter('product_info', 'product')
            ->select('cat.subject_id as category',
                DB::raw('sum(current_sales) as current_sales'),
                DB::raw("string_agg(distinct product.vendor_code::text, ',') as vendor_codes"),
                DB::raw('coalesce(round(sum(current_sales) /
                    NULLIF(round((select sum(current_sales)
                     from product_info as prod
                     where prod.date between ' . $this->getDateFilterTemplate() . '
                     and prod.vendor_code in (' . implode(',',
                        $this->vendorCodes) . ')), 2), 0) * 100, 2), 0) as percent')
            )
            ->crossJoin(DB::raw('lateral (
                          select
                            cat.vendor_code, cat.subject_id
                          from
                            category_vendor as cat
                          where cat.vendor_code = product.vendor_code
                          order by cat.date desc
                          limit 1
                        ) as "cat"'
            ))
            ->groupBy('cat.subject_id')
            ->get();

        return $this->calculatePercentSegmentation($segment);
    }

    /**
     * @inerhitDoc
     * @see DashboardRepositoryInterface::brandOrderedPercent()
     */
    public function brandOrderedPercent(): array
    {
        $segment = $this->initQueryFilter('product_info', 'product')
            ->select('brand.brand_id as brand',
                DB::raw('sum(current_sales) as current_sales'),
                DB::raw("string_agg(distinct product.vendor_code::text, ',') as vendor_codes"),
                DB::raw('coalesce(round(sum(current_sales) /
                    NULLIF(round((select sum(current_sales)
                     from product_info as prod
                     where prod.date between ' . $this->getDateFilterTemplate() . '
                     and prod.vendor_code in (' . implode(',',
                        $this->vendorCodes) . ')), 2), 0) * 100, 2), 0) as percent')
            )
            ->crossJoin(DB::raw('lateral (
                          select
                            brand.vendor_code, brand.brand_id
                          from
                            card_products as brand
                          where brand.vendor_code = product.vendor_code
                        ) as "brand"'
            ))
            ->groupBy('brand.brand_id')
            ->get();

        return $this->calculatePercentSegmentation($segment);
    }

    /**
     * @TODO Need more case for actual logic and do it more simple
     * @param Collection $productPercents
     * @return array
     */
    public function calculatePercentSegmentation(Collection $productPercents)
    {
        $segmentArray = [];

        $segmentGoodPercent = 0;
        $segmentGoodFull = false;
        $segmentArray['good']['count'] = 0;

        $segmentNormalPercent = 0;
        $segmentNormalFull = false;
        $segmentArray['normal']['count'] = 0;

        $segmentBadPercent = 0;
        $segmentArray['bad']['count'] = 0;

        foreach ($productPercents as $product) {

            $productPercent = $product->percent ? round($product->percent, 2) : 0;

            if (($segmentGoodPercent + $productPercent) < 80 && $segmentGoodFull === false) {
                $segmentGoodPercent += $productPercent;
                $segmentArray['good']['vendor_codes'] = array_merge(
                    $segmentArray['good']['vendor_codes'] ?? [],
                    explode(',', $product->vendor_codes)
                );

                $segmentArray['good']['count'] += 1;
                continue;
            }

            $segmentGoodFull = true;

            if ($segmentGoodPercent + $segmentNormalPercent + $productPercent < 96
                && $segmentNormalFull === false) {
                $segmentNormalPercent += $productPercent;
                $segmentArray['normal']['vendor_codes'] = array_merge(
                    $segmentArray['normal']['vendor_codes'] ?? [],
                    explode(',', $product->vendor_codes)
                );
                $segmentArray['normal']['count'] += 1;
                continue;
            }

            $segmentNormalFull = true;

            $segmentBadPercent += $productPercent;
            $segmentArray['bad']['vendor_codes'] = array_merge(
                $segmentArray['bad']['vendor_codes'] ?? [],
                explode(',', $product->vendor_codes)
            );
            $segmentArray['bad']['count'] += 1;
        }

        $segmentArray['good']['percent'] = $segmentGoodPercent ?? 0;

        $segmentArray['normal']['percent'] = $segmentNormalPercent ?? 0;

        $segmentArray['bad']['percent'] = $segmentBadPercent ?? 0;

        return $segmentArray;
    }

    /**
     * @param Collection $productPercentsData
     * @param string $calculateField
     * @return array
     */
    public function calculateGroupPercentSegmentation(Collection $productPercentsData, string $calculateField): array
    {
        $getArrayFromCollections = $productPercentsData->toArray();
        $calculatedArray = [];
        $totalRevenue = array_sum(array_column($getArrayFromCollections, $calculateField));

        foreach ($this->vendorCodes as $key => $vendorCodesGroup) {

            foreach ($getArrayFromCollections as $productData) {
                if (in_array($productData->vendor_codes, $vendorCodesGroup)) {
                    $revenue = $calculatedArray[$key][$calculateField] ?? 0;
                    $calculatedArray[$key][$calculateField] = $revenue + $productData->$calculateField;
                }
            }
            if (isset($calculatedArray[$key][$calculateField])) {
                $calculatedArray[$key]['vendor_codes'] = $key;
                $calculatedArray[$key]['percent'] = ($calculatedArray[$key][$calculateField] / $totalRevenue) * 100;
            }
        }

        array_multisort(array_column($calculatedArray, $calculateField), SORT_DESC, $calculatedArray);
        $dataCollection = collect($calculatedArray)->map(function ($row) {
            return (object) $row;
        });


        return $this->calculatePercentSegmentation($dataCollection);
    }

    /**
     * @return array
     */
    public function getVendorCodesFromGroup(): array
    {
        $resultVendorCodes = [];

        foreach ($this->vendorCodes as $code) {
            if ($code) {
                $resultVendorCodes = array_merge($resultVendorCodes, $code);
            }
        }

        return $resultVendorCodes;
    }
}
