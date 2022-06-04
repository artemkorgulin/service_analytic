<?php

namespace App\Http\Controllers\Api\Wildberries;

use App\Helpers\Controller\CommonControllerHelper;
use App\Http\Requests\Common\CommonCategorySearchRequest;
use App\Http\Resources\Wildberries\AccountCategoryCollection;
use App\Models\WbCategory;
use App\Models\WbProduct;
use App\Repositories\Wildberries\WildberriesCategoryRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Http\Request;

/**
 * Class CategoriesController
 * Хранит категории продуктов Wildberries
 */
class CategoriesController extends CommonController
{

    protected function getParams(Request $request): CommonControllerHelper
    {
        $params = parent::getParams($request);
        if (app()->runningInConsole() === false) {
            foreach ($request->accounts as $account) {
                if ($account['platform_title'] === $params->myPlatformTitle && $account['pivot']['is_selected'] == 1) {
                    $params->account = $account;
                }
            }
        }

        return $params;
    }

    /**
     * Получение всех категорий продуктов Wildberries
     * Параметры:
     * search ищет категории продуктов Wildberries
     *
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categories = WbCategory::select('id', 'name', 'parent')->orderBy('name');
        if ($search && strlen($search) > 2) {
            $categories = $categories->where('name', 'LIKE', $search . '%')->orWhere('parent', 'LIKE', $search . '%');
        }
        $categories = $categories->paginate()->setPath('')->appends(['search' => $search]);
        return response()->json($categories);
    }

    /**
     * Получение категории Wildberries
     * @param Request $request
     * @param $id
     */
    public function show(Request $request, $id)
    {
        return response()->json(WbCategory::select('id', 'name', 'parent', 'data')->firstWhere('id', $id));
    }

    /**
     * Get categories of my product
     */
    public function myCategories(Request $request)
    {
        $params = $this->getParams($request);
        $categories = WbProduct::select('parent', 'object', \DB::raw('COUNT(object) AS count_in_category'))
            ->where('account_id', $params->account['id']);
        if ($request->get('search')) {
            $categories = $categories->where('object', 'LIKE', '%' . $request->get('search') . '%')->orWhere('parent', 'LIKE', '%' . $request->get('search') . '%');
        }
        $categories = $categories->groupBy('object')->orderBy('parent')->orderBy('object')->paginate()->setPath('')->appends(['search' => $request->get('search')]);;
        return response()->json($categories);
    }

    /**
     * @param  CommonCategorySearchRequest  $categorySearchRequest
     * @param  WildberriesCategoryRepository  $categoryRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountProductCategory(
        CommonCategorySearchRequest $categorySearchRequest,
        WildberriesCategoryRepository $categoryRepository
    ) {
        try {
            $query = $categorySearchRequest->input('query');

            $category = $categoryRepository->getAccountProductCategory(
                $query['search'] ?? null
            );

            $categories = PaginatorHelper::addPagination($categorySearchRequest, $category);

            return response()->api_success(
                new AccountCategoryCollection($categories)
            );
        } catch (\Exception $exception) {
            report($exception);
            return  ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
