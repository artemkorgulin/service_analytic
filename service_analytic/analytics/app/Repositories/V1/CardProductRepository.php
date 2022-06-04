<?php

namespace App\Repositories\V1;

use App\Helpers\StatisticQueries;
use App\Contracts\Repositories\V1\CardProductRepositoryInterface;
use App\Models\CardProduct;
use App\Services\GraphForProductService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CardProductRepository implements CardProductRepositoryInterface
{

    /**
     * @param $vendorCode
     * @param $request
     * @return CardProduct|Model|null
     */
    public function getDetailStatistic($vendorCode, $request): CardProduct|Model|null
    {
        $product = CardProduct::query()
            ->selectRaw('
                card_products.vendor_code,
                coalesce(method_aggregate_product_info.total_sales, 0) as total_sales,
                coalesce(ceiling(method_aggregate_product_info.revenue/100)::int, 0) as proceeds,
                method_search_request_avg_start.search_request_start,
                method_search_request_avg_end.search_request_end,
                method_position_avg_start.position_start,
                method_position_avg_end.position_end
            ')
            ->selectRaw('
                coalesce((select pi.grade from product_info pi
                where pi.vendor_code = card_products.vendor_code and pi.date >= ?
                order by pi.date asc
                limit 1), 0) as rating_start
            ', [$request->start_date])
            ->selectRaw('
                coalesce((select pi.grade from product_info pi
                where pi.vendor_code = card_products.vendor_code and pi.date <= ?
                order by pi.date desc
                limit 1), 0) as rating_end
            ', [$request->end_date])
            ->leftJoin(DB::raw(StatisticQueries::getAggregateProductInfo('card_products', $request->start_date,
                $request->end_date)),
                'card_products.vendor_code', '=', 'method_aggregate_product_info.vendor_code')
            ->leftJoin(
                DB::raw(StatisticQueries::getPositionAvgEnd('card_products', $request->start_date, $request->end_date)),
                'card_products.vendor_code', '=', 'method_position_avg_end.vendor_code')
            ->leftJoin(
                DB::raw(StatisticQueries::getPositionAvgStart('card_products', $request->start_date,
                    $request->end_date)),
                'card_products.vendor_code', '=', 'method_position_avg_start.vendor_code')
            ->leftJoin(
                DB::raw(StatisticQueries::getSearchRequestAvgEnd('card_products', $request->start_date,
                    $request->end_date)),
                'card_products.vendor_code', '=', 'method_search_request_avg_end.vendor_code')
            ->leftJoin(
                DB::raw(StatisticQueries::getSearchRequestAvgStart('card_products', $request->start_date,
                    $request->end_date)),
                'card_products.vendor_code', '=', 'method_search_request_avg_start.vendor_code')
            ->where('card_products.vendor_code', $vendorCode)
            ->first();

        if (isset($product)) {
            $graphs = GraphForProductService::graphForDetailPage($product->vendor_code, $request->start_date,
                $request->end_date);
            $product->graphs = collect([
                [
                    'title' => 'Продажи',
                    'graph' => $graphs->mapWithKeys(function ($item) {
                        return [$item->date => $item->sales ?: 0];
                    })
                ],
                [
                    'title' => 'Выручка',
                    'graph' => $graphs->mapWithKeys(function ($item) {
                        return [$item->date => $item->revenue ?: 0];
                    })
                ]
            ]);

            $positionRepository = new PositionRepository();
            $product->positions = $positionRepository->getByVendorCode($vendorCode, $request);

            $searchRequestsRepository = new SearchRequestsRepository();
            $product->search_request = $searchRequestsRepository->getByVendorCode($vendorCode, $request);
            $product->search_request_for_user = $searchRequestsRepository->getByVendorCodeForUser($vendorCode,
                $request);
        }

        return $product;
    }

    /**
     * @param $vendorCode
     * @param $date
     * @return \Staudenmeir\LaravelCte\Query\Builder
     */
    public function getRecommendations($vendorCode): \Staudenmeir\LaravelCte\Query\Builder
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $startDate = Carbon::yesterday()->subDays(7)->format('Y-m-d');

        $lastPositionsTop36 = DB::table('positions as p')
            ->selectRaw('
                        p.web_id, p.subject_id, p.vendor_code,
                        rank() over (partition by "p"."web_id", "p"."subject_id", "p"."position" order by p.date desc)
                    ')
            ->whereBetween('p.date', [$startDate, $yesterday])
            ->whereRaw('(p.subject_id, p.web_id) in (table product_subject_web)')
            ->where('p.position', '<', '37');

        $recommendation = DB::query()
            ->fromSub($lastPositionsTop36, 'positions')
            ->selectRaw('
                positions.web_id,
                positions.subject_id,
                count(positions.vendor_code) as product_quantity,
                round(avg(method_product_info.images_count) filter (where method_product_info.images_count > 0)) as photo_avg,
                min(method_product_info.comments_count) filter (where method_product_info.comments_count > 0) as comments_min,
                round((avg(method_product_info.final_price))/100, 2) as price_avg,
                min(method_product_info.grade) filter (where method_product_info.grade > 0) as rating_min,
                round((avg(method_product_info.grade) filter (where method_product_info.grade > 0)), 2) as rating_avg,
                min(method_product_info.current_sales) filter (where method_product_info.current_sales > 0) as sale_min,
                max(method_product_info.current_sales) filter (where method_product_info.current_sales > 0) as sale_max,
                round(avg(method_product_info.current_sales) filter (where method_product_info.current_sales > 0))::int as sale_avg
            ')
            ->withExpression('product_subject_web', function ($query) use ($vendorCode) {
                $query->select('cv.subject_id', 'cv.web_id')
                    ->from('category_vendor as cv')
                    ->join('category_trees as ct', function ($join) {
                        $join->on('cv.web_id', '=', 'ct.web_id')
                            ->whereNull('ct.deleted_at');
                    })
                    ->where('cv.vendor_code', $vendorCode)
                    ->groupBy('cv.subject_id', 'cv.web_id');
            })
            ->leftJoin(DB::raw(StatisticQueries::getProductInfo('positions', $yesterday)),
                'positions.vendor_code', '=', 'method_product_info.vendor_code')
            ->where('rank', 1)
            ->groupBy('positions.web_id', 'positions.subject_id');

        return DB::query()
            ->fromSub($recommendation, 'r')
            ->selectRaw('
                max(r.photo_avg) as photo,
                max(r.comments_min) as comments,
                max(r.price_avg) as price,
                max(r.rating_min) as rating,
                max(r.rating_avg) as rating_avg
            ');
    }

    public function getRatingList(array $vendorCode): Collection
    {
        return DB::table('card_products')
            ->select('vendor_code', 'grade')
            ->whereIn('vendor_code', $vendorCode)
            ->pluck('vendor_code','grade');
    }

    public function firstByVendorCode(int $vendorCode): CardProduct|Builder|null
    {
        return CardProduct::query()
            ->where('vendor_code', $vendorCode)
            ->first();
    }

}
