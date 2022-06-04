<?php

namespace App\Helpers\RequestParams\Analysis;

use App\Helpers\RequestParams\RequestParams;
use Illuminate\Http\Request;

class AnalysisBrandCategoryParams extends RequestParams
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function getRequestParams(Request $request): array
    {
        return array_merge(parent::getRequestParams($request), [
            'brandId' => $request->input('brand_id') ?? null,
            'subjectId' => $request->input('subject_id') ?? null,
            'min' => $request->input('min') ?? null,
            'max' => $request->input('max') ?? null,
            'segment' => $request->input('segment'),
            'categoryId' => $request->input('category_id') ?? null,
        ]);
    }
}
