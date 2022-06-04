<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\RequestParams\CategoryAnalysisParams;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategoryAnalysisRequest;
use App\Repositories\V2\Categories\CategoryAnalysisRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;

class CategoryAnalysisController extends Controller
{
    /**
     * @param CategoryAnalysisRequest $request
     * @param CategoryAnalysisParams $requestParams
     * @return mixed
     */
    public function analysis(CategoryAnalysisRequest $request, CategoryAnalysisParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $result = (new CategoryAnalysisRepository($requestParams))->getCachedAnalysis();

            QueryBuilderHelper::saveUserParams($request,
                ['brands', 'start_date', 'end_date', 'filters', 'sort', 'columns', 'columns_order']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
