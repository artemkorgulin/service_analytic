<?php

namespace App\Http\Controllers;

use App\Http\Requests\WbUsingKeywordRequest;
use App\Models\WbUsingKeyword;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Exception;
use Illuminate\Support\Facades\Log;

class WbUsingKeywordController extends Controller
{
    /**
     * @param  WbUsingKeywordRequest  $request
     * @return mixed
     */
    public function index(WbUsingKeywordRequest $request)
    {
        try {
            $query = WbUsingKeyword::select('wb_product_id', 'name', 'wb_products.nmid AS sku', 'user_id')
                ->join('wb_products', 'wb_products.id', '=', 'wb_using_keywords.wb_product_id');

            if ($request->input('date')) {
                $query->whereDate('wb_using_keywords.created_at', $request->input('date'));
            }

            $result = PaginatorHelper::addPagination($request, $query);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return response()->api_fail(trans('errors.method_failed'));
        }
    }
}
