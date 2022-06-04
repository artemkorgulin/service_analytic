<?php

namespace App\Http\Controllers;

use App\Http\Requests\OzUserCategoriesRequest;
use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Models\WbCategory;
use App\Repositories\CategoriesForSearchOptimizationRepository;
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
    public function index(Request $request) {
        $name = $request->get('name');
        $parent_id = $request->get('parent_id');
        $categoriesQuery = OzCategory::select(['id', 'name', 'external_id', 'parent_id', 'settings'])->orderBy('name');
        if ($parent_id && !$name) {
            $categories = $categoriesQuery->children($parent_id)->get();
        } elseif ($name) {
            $categories = $categoriesQuery->where('name', 'LIKE', '%'.$name.'%')->with(['parents'])->get();
        } else {
            $categories = $categoriesQuery->roots()->get();
        }
        return response()->json($categories);
    }

    /**
     * Get all Ozon products types
     * @return \Illuminate\Http\JsonResponse
     */
    public function params(Request $request) {

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
    public function param(Request $request) {
        $id = $request->id;
        $category = OzCategory::select(['id', 'name', 'external_id', 'parent_id', 'settings'])
            ->firstWhere('id', $id);
        return response()->json($category);
    }

    /**
     *
     * @param Request $request
     */
    public function getDirectoryValues(Request $request) {

    }

    /**
     * Get categories for search optimization.
     *
     * @param OzUserCategoriesRequest $request
     * @return mixed
     */
    public function getCategoriesForSearchOptimization(OzUserCategoriesRequest $request, CategoriesForSearchOptimizationRepository $categoriesForSearchOptimizationRepository)
    {
        $result = [];

        $category = trim($request->input('category'));

        $result['parent_categories'] = $categoriesForSearchOptimizationRepository->getWbCategoryAnalogOz($category);

        $result['all_subjects'] = WbCategory::whereIn('parent', $result['parent_categories'])->pluck('name');

        return response()->api_success($result);
    }
}
