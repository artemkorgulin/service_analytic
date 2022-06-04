<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GraphForProductService
{
    public static function graphsForReport(Collection $vendorCodes, string $startDate, string $endDate): Collection
    {
        $vendorCodes = $vendorCodes->join(',');
        return DB::query()
            ->withExpression('gen_date', function ($query) use ($vendorCodes, $startDate, $endDate) {
                $query->fromRaw("public.generated_graph('$startDate'::date, '$endDate'::date, array[$vendorCodes])");
            })
            ->selectRaw("
                g.vendor_code, string_agg(coalesce(p.current_sales, 0)::text, ',' order by g.date) as graph_sales,
                string_agg(coalesce(p.category_count, 0)::text, ',' order by g.date) as graph_category_count,
                string_agg(coalesce(p.final_price/100, 0)::text, ',' order by g.date) as graph_price,
                string_agg(coalesce(p.stock_count, 0)::text, ',' order by g.date) as graph_stock
            ")
            ->from('gen_date as g')
            ->leftJoin('product_info as p', function ($join) {
                $join->on('p.vendor_code', '=', 'g.vendor_code')
                    ->on('p.date', '=', 'g.date');
            })
            ->groupBy('g.vendor_code')
            ->get()
            ->keyBy('vendor_code');
    }

    public static function graphForDetailPage(int $vendorCodes, string $startDate, string $endDate): Collection
    {
        return DB::query()
            ->withExpression('gen_date', function ($query) use ($vendorCodes, $startDate, $endDate) {
                $query->fromRaw("public.generated_graph('$startDate'::date, '$endDate'::date, array[$vendorCodes])");
            })
            ->selectRaw("
                g.date, coalesce(p.current_sales, 0) as sales,
                coalesce(p.revenue/100::int, 0) as revenue
            ")
            ->from('gen_date as g')
            ->leftJoin('product_info as p', function ($join) {
                $join->on('p.vendor_code', '=', 'g.vendor_code')
                    ->on('p.date', '=', 'g.date');
            })
            ->orderBy('g.date')
            ->get();
    }
}