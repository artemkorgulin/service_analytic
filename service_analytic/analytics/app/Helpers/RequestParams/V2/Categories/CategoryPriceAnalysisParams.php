<?php

namespace App\Helpers\RequestParams\V2\Categories;

use App\Helpers\RequestParams\RequestParams;
use Illuminate\Http\Request;

class CategoryPriceAnalysisParams extends RequestParams
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function getRequestParams(Request $request): array
    {
        return array_merge(parent::getRequestParams($request), [
            'category' => $request->input('category'),
            'segmentCount' => $request->input('segment'),
            'minPrice' => $request->input('min'),
            'maxPrice' => $request->input('max')
        ]);
    }
}