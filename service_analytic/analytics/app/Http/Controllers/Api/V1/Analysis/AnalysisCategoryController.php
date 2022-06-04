<?php

namespace App\Http\Controllers\Api\V1\Analysis;

use App\Helpers\Analysis\AnalysisHelper;
use App\Helpers\Analysis\QueryConditionAnalysisCategory;
use App\Helpers\RequestParams\Analysis\AnalysisBrandCategoryParams;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Analysis\AnalysisCategoryRequest;
use App\Repositories\V1\Analysis\AnalysisCategoryRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnalysisCategoryController extends Controller
{

    /**
     * @return mixed
     */
    public function analysisCategory(
        AnalysisCategoryRequest $request
    ): mixed {
        $requestParams = (new AnalysisBrandCategoryParams())->getRequestParams($request);
        $analysisCategoryRepository = new analysisCategoryRepository();
        $analysisHelper = new AnalysisHelper();

        try {
            // Категории
            $analysisCategoryRepository->findBySubjectId($requestParams['subjectId']);
        } catch (ModelNotFoundException $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }

        try {
            $queryCondition = new QueryConditionAnalysisCategory();
            $selectBlock = $queryCondition->getSelectParams();

            $data = $analysisCategoryRepository->getAnalysis($requestParams['categoryId'], $requestParams['subjectId'],
                $requestParams['startDate'], $requestParams['endDate'], $selectBlock)
                    ->where('web_id', '=', $requestParams['categoryId'])->get();
            // Сегменты
            $segments = $analysisHelper->getSegments($data, $requestParams);

            $result['subject_id'] = $requestParams['subjectId'];
            $result['category_id'] = $requestParams['categoryId'];
            $result['take'] = $data->sum('take');
            $result['products'] = $data->count();
            $result['table'] = $analysisHelper->getSegmentDataCategories($data, $segments);

            return response()->api_success($result);
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
