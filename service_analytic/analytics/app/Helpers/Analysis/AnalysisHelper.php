<?php

namespace App\Helpers\Analysis;

use Illuminate\Support\Collection;

class AnalysisHelper
{
    /**
     * @return array
     */
    public function getSegments(Collection $data, array $requestParams): array
    {
        if ($requestParams['min'] === null or $requestParams['max'] === null) {
            $requestParams['max'] = (int) $data->max('last_price');
            $requestParams['min'] = (int) $data->min('last_price');
        }

        $step = round(($requestParams['max'] - $requestParams['min']) / $requestParams['segment']);
        $segments = [];
        for ($i = 0; $i < $requestParams['segment']; $i++) {
            if ($i === 0) {
                $segments[$i]['min'] = $requestParams['min'];
                $segments[$i]['max'] = $requestParams['min'] + $step;
            } else {
                $segments[$i]['min'] = $segments[$i - 1]['max'] + 1;
                $segments[$i]['max'] = $segments[$i - 1]['max'] + $step;
            }
        }

        return $segments;
    }

    public function prepareSegmentDataBrands(Collection $data): array
    {
        $result = [];

        if ($count = $data->count()) {
            $result['products'] = $count;
            $result['products_with_sale'] = $this->countProductWithSale($data);
            $result['avg_price'] = round($data->sum('avg_price') / $count);;
            $result['take'] = round($data->sum('take'));
            $result['count_suppliers'] = $this->countSuppliers($data);
            $result['count_suppliers_with_sale'] = $this->countSuppliersWithSale($data);
            // Среднее число продаж товара = sales / product_with_sales
            $result['avg_sale_count_one_product'] = $this->avgSaleCountProduct($data);
            // Выручка на товар = Выручка/товаров
            $result['avg_price_one_product'] = round($data->sum('take') / $count);
            $result['count_subject_id'] = $data->groupBy('subject_id')->count();
        }

        return $result;
    }

    public function getSegmentDataBrands(Collection $data, array $segments): array
    {
        $resultSegment = [];
        foreach ($segments as $segment) {
            $dataSegment = $data->whereBetween('last_price', [$segment['min'], $segment['max']]);
            $dataSegment = $this->prepareSegmentDataBrands($dataSegment);
            $resultSegment[$segment['min'].'-'.$segment['max']] = $dataSegment;
        }

        return $resultSegment;
    }

    public function prepareSegmentDataCategories(Collection $data): array
    {
        $result = [];

        if ($count = $data->count()) {
            $result['products'] = $count;
            $result['products_with_sale'] = $this->countProductWithSale($data);
            $result['avg_price'] = round($data->sum('avg_price') / $count);
            $result['take'] = round($data->sum('take'));
            // Среднее число продаж товара = sales / product_with_sales
            $result['avg_sale_count_one_product'] = $this->avgSaleCountProduct($data, $result['products_with_sale']);
            $result['count_suppliers'] = $this->countSuppliers($data);
            $result['count_suppliers_with_sale'] = $this->countSuppliersWithSale($data);
            $result['count_subject_id'] = $data->groupBy('subject_id')->count();
            // Выручка на товар = Выручка/товаров
            $result['avg_price_one_product'] = round($data->sum('take') / $count);
        }

        return $result;
    }

    public function getSegmentDataCategories(Collection $data, array $segments): array
    {
        $resultSegment = [];
        foreach ($segments as $segment) {
            $dataSegment = $data->whereBetween('last_price', [$segment['min'], $segment['max']]);
            $segmentData = $this->prepareSegmentDataCategories($dataSegment);
            $resultSegment[$segment['min'].'-'.$segment['max']] = $segmentData;
        }

        return $resultSegment;
    }

    public function countSuppliers(Collection $dataSegment): int
    {
        return $dataSegment->unique('supplier_id')->count();
    }

    public function countSuppliersWithSale(Collection $dataSegment): int
    {
        return $dataSegment->unique('supplier_id')->where('total_sales', '>', 0)->count();
    }

    public function countProductWithSale(Collection $dataSegment): int
    {
        return $dataSegment->unique('vendor_code')->where('total_sales', '>', 0)->count();
    }

    public function avgSaleCountProduct(Collection $dataSegment): int
    {
        return $dataSegment->sum('total_sales');
    }
}
