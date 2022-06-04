<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Brand\BrandFindRequest;
use App\Http\Requests\V1\Brand\BrandGetRequest;
use App\Models\Brand;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;

class BrandController extends Controller
{
    public function find(BrandFindRequest $request)
    {
        try {
            $brands = Brand::select('brand', 'brand_id')->where('brand', 'ILIKE', '%'.$request->input('filter').'%')->get()->keyBy('brand_id');

            return response()->api_success($brands);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    public function get(BrandGetRequest $request)
    {
        $result = null;

        $brand = Brand::select('brand', 'brand_id')->where('brand_id', $request->input('id'))->first();

        if ($brand) {
            $result = [
                'brand_id' => $brand->brand_id,
                'brand' => $brand->brand,
            ];
        }

        return $result;
    }
}
