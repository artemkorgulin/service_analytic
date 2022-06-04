<?php

namespace App\Http\Controllers\Api\V2\Category;

use App\Helpers\RequestParams\V2\Categories\CategoryPriceAnalysisParams;
use App\Helpers\RequestParams\V2\Categories\CategoryStatisticParams;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Categories\CategoryPriceAnalysisFilterRequest;
use App\Http\Requests\V2\Categories\CategoryStatisticFilterRequest;
use App\Repositories\V2\Categories\CategoryStatisticRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * @param  CategoryStatisticFilterRequest  $request
     * @param  CategoryStatisticParams  $requestParams
     * @return JsonResponse
     */
    public function getProducts(
        CategoryStatisticFilterRequest $request,
        CategoryStatisticParams $requestParams
    ): JsonResponse {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new CategoryStatisticRepository($requestParams))->getCachedProducts(
                $requestParams['filters'],
                $requestParams['sort'],
                $requestParams['currentPage'],
                $requestParams['perPage']
            );
            return response()->api_success($result);
        } catch (Exception $e) {
            report($e);
            return ExceptionHandlerHelper::logAndSendFailResponse($e);
        }
    }

    /**
     * @param  CategoryStatisticFilterRequest  $request
     * @param  CategoryStatisticParams  $requestParams
     * @return JsonResponse
     */
    public function getSubcategories(
        CategoryStatisticFilterRequest $request,
        CategoryStatisticParams $requestParams
    ): JsonResponse {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new CategoryStatisticRepository($requestParams))->getCachedSubcategories();
            return response()->api_success($result);
        } catch (Exception $e) {
            report($e);
            return ExceptionHandlerHelper::logAndSendFailResponse($e);
        }
    }

    /**
     * @param  CategoryPriceAnalysisFilterRequest  $request
     * @param  CategoryPriceAnalysisParams  $requestParams
     * @return JsonResponse
     */
    public function getPriceAnalysis(
        CategoryPriceAnalysisFilterRequest $request,
        CategoryPriceAnalysisParams $requestParams
    ): JsonResponse {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new CategoryStatisticRepository($requestParams))->getCachedPriceAnalysis(
                $requestParams['segmentCount'],
                $requestParams['minPrice'], $requestParams['maxPrice']
            );
            return response()->api_success($result);
        } catch (Exception $e) {
            report($e);
            return ExceptionHandlerHelper::logAndSendFailResponse($e);
        }
    }

}
