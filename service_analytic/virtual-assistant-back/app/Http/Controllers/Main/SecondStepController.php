<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Repositories\SearchQueryRepository;
use Illuminate\Http\Request;

/**
 * Class SecondStepController
 * Шаг 2 виртуального помощника
 * Работа с поисковыми запросами
 *
 * @package App\Http\Controllers\Main
 */
class SecondStepController extends Controller
{
    /**
     * Получение спсика поисковых запросов
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $request->validate(
            [
                'ozonCategoryId' => 'required|int',
                'productTitle' => 'required|string',
                'brand' => 'required|string',
            ]
        );

        $ozonCategoryId = $request->get('ozonCategoryId');
        $rootQueryTitle = $request->get('productTitle');
        $brand = $request->get('brand');

        $searchQueries = SearchQueryRepository::findByRootQueryInCategory($ozonCategoryId, $rootQueryTitle);

        $response = [
            'searchQueries' => $searchQueries,
            'brand' => $brand,
        ];

        return response()->api(
            true,
            $response,
            []
        );
    }
}
