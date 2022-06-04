<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\CommonCategorySearchRequest;
use App\Models\Feature;
use App\Models\OzCategory;
use App\Repositories\OzonCategoryRepository;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Http\Request;

/**
 * Class OzCategoriesController
 * Хранит категории продуктов Ozon
 * @package App\Http\Controllers
 */
class OzCategoriesController extends Controller
{
    /**
     * Получение всех категорий продуктов Ozon
     *
     * Параметры:
     * @parent_id int - выводит потомков (на уровень ниже (-1))
     * @name string - ищет по like вхождение всех слов и возвращает родителей (всех до корневого уровня)
     * @param Request $request
     * Комбинировать параметры нельзя то есть если выбираем и name и parent_id
     * вернутся все товары с вхождением name из всех категорий
     * Если просто использовать метод без параметров - вернутся все корневые категории Ozon 18+, Автомобили и т.д.
     * Сортировка пока не предусмотрена
     * При работе с API озон необходимо использовать значение external_id для категории
     * При работа с локальной базой id категории
     *
     */
    public function index(Request $request)
    {
        $name = $request->get('name');
        $parent_id = $request->get('parent_id');
        $categoriesQuery = OzCategory::query()
            ->leftJoinSub(
                "SELECT name as sub_name, id as sub_id FROM oz_categories",
                'parent',
                'parent_id',
                '=',
                'parent.sub_id'
            )
            ->select(['id', 'name', 'external_id', 'parent_id', 'parent.sub_name as parent_name', 'settings'])
            ->orderBy('name');

        if ($parent_id && !$name) {
            $categories = $categoriesQuery->children($parent_id)->get();
        } elseif ($name) {
            $categories = $categoriesQuery->where('name', 'LIKE', '%' . $name . '%')->with(['parents'])->get();
        } else {
            $categories = $categoriesQuery->roots()->get();
        }

        return response()->json($categories);
    }

    /**
     * Get all Ozon products types
     * @return \Illuminate\Http\JsonResponse
     */
    public function params(Request $request)
    {
        $categories = OzCategory::select(['id', 'name', 'external_id', 'parent_id', 'settings'])->orderBy('name')
            ->whereNotNull('settings')->where('settings', '<>', '')
            ->search($request->get('search'))->paginate();
        return response()->json($categories);
    }

    /**
     * Get Ozon product type concrete
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function param(Request $request)
    {
        $id = $request->id;
        $category = OzCategory::select(['id', 'name', 'external_id', 'parent_id', 'settings'])
            ->firstWhere('id', $id);
        return response()->json($category);
    }

    /**
     * Возвращает все типы продуктов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductTypes(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(OzCategory::select(['id', 'name', 'parent_id'])->whereNotIn(
            'id', OzCategory::select(['parent_id'])
            ->whereNotNull('parent_id')
            ->pluck('parent_id')->toArray())
            ->search($request->name ?? ($request->search ?? ''))
            ->paginate());
    }

    /**
     * @param CommonCategorySearchRequest $categorySearchRequest
     * @param OzonCategoryRepository $categoryRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountProductCategory(
        CommonCategorySearchRequest $categorySearchRequest,
        OzonCategoryRepository $categoryRepository
    )
    {
        try {
            $query = $categorySearchRequest->input('query');

            $categories = $categoryRepository->getAccountProductCategory(
                UserService::getUserId(),
                UserService::getAccountId(),
                $query['search'] ?? ''
            );

            $categories = PaginatorHelper::addPagination($categorySearchRequest, $categories);

            return response()->api_success(
                $categories
            );
        } catch (\Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Получение полей продукта по его категории
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductTypeFields($id)
    {
        return response()->json(OzCategory::firstWhere('id', $id)->features()->get());
    }

    /**
     * Возвращает все значения справочника по ID характеристики
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getDirectoryValues(Request $request, $id): mixed
    {
        $feature = Feature::findOrFail($id);
        $categoryId = $request->input('category') ?? null;
        $featureOptions = $feature->options($categoryId)->select([
            'oz_feature_options.id',
            'value',
            'popularity'
        ]);
        if (!empty($request->search)) {
            $searchLike = sprintf('%%%s%%', trim($request->name ?? ($request->value ?? ($request->search))));
            $featureOptions = $featureOptions->where('value', 'LIKE', $searchLike);
        }
        $featureOptions = $featureOptions->orderBy('popularity', 'DESC')
            ->orderBy('value', 'ASC')
            ->take(100)
            ->get();
        return response()->api_success($featureOptions);
    }
}
