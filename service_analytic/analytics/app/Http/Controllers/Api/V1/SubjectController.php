<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SubjectRequest;
use App\Models\WbProduct;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * @param SubjectRequest $request
     * @return mixed
     */
    public function index(SubjectRequest $request)
    {
        try {
            $result = [];

            $productId = $request->input('product_id');
            $product = WbProduct::where('id', $productId)->firstOrFail();

            $result['brand'] = $product->brand;

            $result['parent_categories'] = [$product->parent];

            $result['all_subjects'] = DB::connection('va')
                ->table('wb_categories')
                ->where('parent', $product->parent)
                ->pluck('name');

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
