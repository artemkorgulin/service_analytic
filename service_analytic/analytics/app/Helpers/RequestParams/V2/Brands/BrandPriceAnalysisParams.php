<?php

namespace App\Helpers\RequestParams\V2\Brands;

use App\Helpers\RequestParams\RequestParams;
use Illuminate\Http\Request;

class BrandPriceAnalysisParams extends RequestParams
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function getRequestParams(Request $request): array
    {
        return array_merge(parent::getRequestParams($request), [
            'brand' => $request->input('brand'),
            'segmentCount' => $request->input('segment'),
            'minPrice' => $request->input('min'),
            'maxPrice' => $request->input('max')
        ]);
    }
}
