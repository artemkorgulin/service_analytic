<?php

namespace App\Helpers\RequestParams;


use App\Contracts\ParserParams;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RequestParams
{
    const PER_PAGE = 100;
    const PAGE = 1;
    const DIFF_DAYS = 29;

    /**
     * @param  Request  $request
     * @return array
     */
    public function getRequestParams(Request $request): array
    {
        $dateNow = date('Y-m-d');
        if ($request->input('end_date') === null || $request->input('end_date') > $dateNow) {
            $result['endDate'] = Carbon::yesterday()->format('Y-m-d');
        } else {
            $result['endDate'] = Carbon::parse($request->input('end_date'))->format('Y-m-d');
        }

        if ($request->input('start_date') === null) {
            $result['startDate'] = Carbon::parse($result['endDate'])->subDays(self::DIFF_DAYS)->format('Y-m-d');
        } else {
            $result['startDate'] = Carbon::parse($request->input('start_date'))->format('Y-m-d');
        }

        $startParser = Carbon::parse(ParserParams::DATE_START)->format('Y-m-d');
        if ($result['startDate'] < $startParser) {
            $result['startDate'] = $startParser;
        }

        $result['diffDate'] = Carbon::parse($result['endDate'])->diffInDays($result['startDate']) + 1;

        $sort = $request->input('sort');
        if (isset($sort['col']) && !isset($sort['sort'])) {
            $sort['sort'] = 'ASC';
        }

        $result['sort'] = $sort;

        $result['perPage'] = $request->input('per_page', self::PER_PAGE);
        $result['currentPage'] = $request->input('page', self::PAGE);
        $result['filters'] = $request->input('filters');

        return $result;
    }
}
