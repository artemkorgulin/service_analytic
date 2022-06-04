<?php

namespace App\Helpers\RequestParams;


use Illuminate\Http\Request;

class BrandParams extends RequestParams
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function getRequestParams(Request $request): array
    {
        return array_merge(parent::getRequestParams($request), [
            'brandId' => $request->input('brand_id'),
        ]);
    }
}
