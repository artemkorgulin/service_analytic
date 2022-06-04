<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\QueryConditionCategorySubcategry;
use App\Helpers\RequestParams\CategoryParams;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategoryFilterRequest;
use App\Repositories\V1\Categories\CategoryStatisticRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;

class CategoryStatisticController extends Controller
{
    /**
     * @param  CategoryFilterRequest  $request
     * @param  CategoryParams  $requestParams
     * @return mixed
     */
    public function getSubcategories(CategoryFilterRequest $request, CategoryParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);

            $categoryStatisticRepository = new CategoryStatisticRepository($requestParams);

            $queryCondition = new QueryConditionCategorySubcategry();
            $selectBlock = $queryCondition->getSelectParams();

            $query = $categoryStatisticRepository->getCategories($selectBlock);
            if (is_string($query)) {
                return response()->api_success($query);
            }

            $queryCondition->prepare($query, $requestParams['filters']);

            if ($requestParams['sort']) {
                $query->orderBy($requestParams['sort']['col'], $requestParams['sort']['sort']);
            } else {
                $query->orderBy('take', 'desc');
            }

            $result = $query->get();

            QueryBuilderHelper::saveUserParams($request, ['category_id', 'start_date', 'end_date', 'filters', 'sort', 'columns', 'columns_order']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
