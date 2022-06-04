<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategorySubjectsRequest;
use App\Repositories\V1\Categories\CategorySubjectsRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;

class CategorySubjectsController extends Controller
{

    /**
     * @param CategorySubjectsRequest $request
     * @param CategorySubjectsRepository $categorySubjectsRepository
     * @return mixed
     */
    public function getSubjects(CategorySubjectsRequest $request, CategorySubjectsRepository $categorySubjectsRepository)
    {
        try {
            $result = $categorySubjectsRepository->getCategories($request->input('category_id'));

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
