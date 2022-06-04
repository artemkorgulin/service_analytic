<?php

namespace App\Helpers\RequestParams\Categories;

use App\Helpers\RequestParams\RequestParams;
use Illuminate\Http\Request;

class CategoryProductParams extends RequestParams
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function getRequestParams(Request $request): array
    {
        return array_merge(parent::getRequestParams($request), [
            'categoryId' => $request->input('category_id'),
            'subjectId' => $request->input('subject_id'),
        ]);
    }
}