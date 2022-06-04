<?php

namespace App\Repositories\V1;

use App\Contracts\Repositories\V1\PositionRepositoryInterface;
use App\Helpers\StatisticQueries;
use App\Models\Ozon\Position;
use App\Models\Ozon\SearchRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OzPositionRepository implements PositionRepositoryInterface
{
    const TOP36 = 36;

    public function getByVendorCode($vendorCode, $request)
    {
        return Position::selectRaw("
                        positions.date,
                        method_breadcrumbs_category.category,
                        avg(positions.position)::int as position
                    ")->join(DB::raw(StatisticQueries::getBreadcrumbsCategory('positions')), 'positions.web_id', '=', 'method_breadcrumbs_category.web_id')->whereBetween('positions.date', [
                $request->start_date,
                $request->end_date,
            ])->where('positions.vendor_code', $vendorCode)->groupBy('positions.date', 'method_breadcrumbs_category.category')->orderBy('date')->get();
    }

    /**
     * @param $product
     * @param string $date
     * @return null|Collection
     */
    public function getPositionCategory($product, string $date): Collection|null
    {
        return Position::query()->where('date', '=', $date)->where('web_id', '=', $product->category_id)->orderBy('position', 'asc')->limit(self::TOP36)->get();
    }

    /**
     * @param $positionCategoryCollect
     * @param string $date
     * @return null|Collection
     */
    public function getPositionSearch($positionCategoryCollect, string $date): Collection|null
    {
        return SearchRequest::query()
                ->where('date', '=', $date)
                ->whereIn('vendor_code', $positionCategoryCollect->pluck('vendor_code')->toArray())
                ->isActive()->pluck('position');
    }

    /**
     * @param $product
     * @param string $date
     * @return null|Collection
     */
    public function getPositionSearchByProduct($product, string $date): null|int
    {
        return round(SearchRequest::query()->where('date', '=', $date)->where('is_active', '=', 1)->where('vendor_code', '=', $product->sku_fbo)->pluck('position')->avg());
    }

    /**
     * @param int $subjectId
     * @param string $startDate
     * @param string $endDate
     * @return SearchRequest
     */
    public function getSearchPositionBySubjectId(int $subjectId, string $startDate, string $endDate): SearchRequest
    {
        return SearchRequest::selectRaw("
                        search_requests.date,
                        avg(search_requests.position)::int as position
                    ")->whereBetween('search_requests.date', [
                $startDate,
                $endDate,
            ])->where('search_requests.subject_id', $subjectId)->groupBy('search_requests.date')->orderBy('search_requests.date')->first();
    }
}
