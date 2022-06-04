<?php

namespace App\Http\Controllers\Api\V1\Analysis;

use App\Helpers\Analysis\AnalysisHelper;
use App\Helpers\Analysis\QueryConditionAnalysisBrand;
use App\Helpers\RequestParams\Analysis\AnalysisBrandCategoryParams;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Analysis\AnalysisBrandRequest;
use App\Repositories\V1\Analysis\AnalysisBrandRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnalysisBrandController extends Controller
{
    /**
     * @param  AnalysisBrandRepository  $analysisBrandRepository
     * @param  AnalysisBrandRequest  $request
     * @param  AnalysisBrandCategoryParams  $requestParams
     * @param  AnalysisHelper  $analysisHelper
     * @return mixed
     */
    public function analysisBrand(
        AnalysisBrandRepository $analysisBrandRepository,
        AnalysisBrandRequest $request,
        AnalysisBrandCategoryParams $requestParams,
        AnalysisHelper $analysisHelper,
    ): mixed {

        $requestParams = $requestParams->getRequestParams($request);

        try {
            // Бренды
            $analysisBrandRepository->findByBrandId($requestParams['brandId']);
        } catch (ModelNotFoundException $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }

        try {
            $queryCondition = new QueryConditionAnalysisBrand();
            $selectBlock = $queryCondition->getSelectParams();

            $data = $analysisBrandRepository->getAnalysis($requestParams['brandId'],
                $requestParams['startDate'], $requestParams['endDate'], $selectBlock)->get();

            // Сегменты
            $segments = $analysisHelper->getSegments($data, $requestParams);

            $result['brand_id'] = $requestParams['brandId'];
            $result['take'] = $data->sum('take');
            $result['products'] = $data->count();
            $result['table'] = $analysisHelper->getSegmentDataBrands($data, $segments);

            return response()->api_success($result);
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
