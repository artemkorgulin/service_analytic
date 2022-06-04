<?php


namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Repositories\OzonCategoryRepository;
use App\Repositories\RootQueryRepository;
use Illuminate\Http\Request;

/**
 * Class FirstStepController
 * Шаг 1 виртуального помощника
 * Данные о товаре
 *
 * @package App\Http\Controllers\Main
 */
class FirstStepController extends Controller
{
    /**
     * Точка входа
     *
     * @return mixed
     */
    public function index()
    {
        return response()->api(
            true,
            [],
            []
        );
    }

    /**
     * Получение списка корневых запросов к введенному слову
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getRootQueries(Request $request)
    {
        $request->validate(
            [
                'productTitle' => 'required|string',
            ]
        );

        $productTitle = $request->get('productTitle');

        $rootQueries = RootQueryRepository::findLikeTitle($productTitle)->pluck('name');
        $rootQueriesList = array_unique($rootQueries->toArray());

        return response()->api(
            true,
            [
                'rootQueriesList' => $rootQueriesList,
            ],
            []
        );
    }

    /**
     * Получение списка категорий Озон к введенному корневому запросу
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getOzonCategories(Request $request)
    {
        $request->validate(
            [
                'productTitle' => 'required|string',
            ]
        );

        $rootQueryTitle = $request->get('productTitle');
        $categories = OzonCategoryRepository::findByRootQueryTitle($rootQueryTitle);

        return response()->api(
            true,
            [
                'ozonCategoriesList' => $categories,
            ],
            []
        );
    }
}
