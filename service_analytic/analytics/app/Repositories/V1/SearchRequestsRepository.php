<?php

namespace App\Repositories\V1;

use App\Contracts\Repositories\V1\SearchRequestsRepositoryInterface;
use App\Models\SearchRequest;
use Illuminate\Database\Eloquent\Collection;

class SearchRequestsRepository implements SearchRequestsRepositoryInterface
{

    /**
     * @param $vendorCode
     * @param $request
     * @return Collection
     */
    public function getByVendorCode($vendorCode, $request): Collection
    {
        $searchRequestMaxDate = SearchRequest::selectRaw('
                date, max(created_at) as sort_date,
                relation_id, vendor_code
            ')
            ->where('vendor_code', $vendorCode)
            ->where('relation', 'rufago')
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->groupBy('relation_id', 'date', 'vendor_code');

        return SearchRequest::query()
            ->selectRaw('
                search_requests.position, search_requests.date, search_requests.relation,
                r.search_response as "search"
            ')
            ->withExpression('max_date', $searchRequestMaxDate)
            ->join('max_date', function ($join) {
                $join->on('max_date.vendor_code', '=', 'search_requests.vendor_code')
                    ->on('max_date.sort_date', '=', 'search_requests.created_at');
            })
            ->join('rufago as r', function ($join) {
                $join->on('r.id', '=', 'search_requests.relation_id')
                    ->where('search_requests.relation', 'rufago');
            })
            ->where('search_requests.vendor_code', $vendorCode)
            ->whereBetween('search_requests.date', [$request->start_date, $request->end_date])
            ->get();
    }

    /**
     * @param $vendorCode
     * @param $request
     * @return Collection
     */
    public function getByVendorCodeForUser($vendorCode, $request): Collection
    {
        $userId = $request['user']['id'];
        $searchRequestMaxDate = SearchRequest::selectRaw('
                date, max(created_at) as sort_date,
                relation_id, vendor_code
            ')
            ->where('vendor_code', $vendorCode)
            ->where('relation', 'user_observers')
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->groupBy('relation_id', 'date', 'vendor_code');

        return SearchRequest::query()
            ->selectRaw('
                search_requests.position, search_requests.date, search_requests.relation,
                ko.keyword as "search"
            ')
            ->withExpression('max_date', $searchRequestMaxDate)
            ->join('max_date', function ($join) {
                $join->on('max_date.vendor_code', '=', 'search_requests.vendor_code')
                    ->on('max_date.sort_date', '=', 'search_requests.created_at');
            })
            ->join('user_observers as uo', function ($join) use ($userId) {
                $join->on('uo.id', '=', 'search_requests.relation_id')
                    ->where('search_requests.relation', 'user_observers')
                    ->where('uo.user_id', $userId);
            })
            ->join('keywords_observers as ko', 'ko.id', '=', 'uo.keyword_id')
            ->where('search_requests.vendor_code', $vendorCode)
            ->whereBetween('search_requests.date', [$request->start_date, $request->end_date])
            ->get();
    }
}
