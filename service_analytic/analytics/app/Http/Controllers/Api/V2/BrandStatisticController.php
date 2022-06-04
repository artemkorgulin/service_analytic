<?php

namespace App\Http\Controllers\Api\V2;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\RequestParams\V2\Brands\BrandParams;
use App\Helpers\RequestParams\V2\Brands\BrandPriceAnalysisParams;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Brands\BrandFilterRequest;
use App\Http\Requests\V2\Brands\BrandPriceAnalysisFilterRequest;
use App\Repositories\V2\Brands\BrandStatisticRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class BrandStatisticController extends Controller
{
    /**
     * @param  BrandFilterRequest  $request
     * @param  BrandParams  $requestParams
     * @return JsonResponse
     */
    public function getProducts(BrandFilterRequest $request, BrandParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new BrandStatisticRepository($requestParams))->getCachedProducts(
                $requestParams['filters'],
                $requestParams['sort'],
                $requestParams['currentPage'],
                $requestParams['perPage']
            );

            QueryBuilderHelper::saveUserParams($request, ['brand', 'start_date', 'end_date']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  BrandFilterRequest  $request
     * @param  BrandParams  $requestParams
     * @return JsonResponse
     */
    public function getCategories(BrandFilterRequest $request, BrandParams $requestParams): JsonResponse
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new BrandStatisticRepository($requestParams))->getCachedCategories();

            QueryBuilderHelper::saveUserParams($request, ['brand', 'start_date', 'end_date']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    public function getSellers(BrandFilterRequest $request, BrandParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new BrandStatisticRepository($requestParams))->getCachedSellers();

            QueryBuilderHelper::saveUserParams($request, ['brand', 'start_date', 'end_date']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    public function getPriceAnalysis(BrandPriceAnalysisFilterRequest $request, BrandPriceAnalysisParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new BrandStatisticRepository($requestParams))->getCachedPriceAnalysis(
                $requestParams['segmentCount'],
                $requestParams['minPrice'],
                $requestParams['maxPrice']
            );

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

}
