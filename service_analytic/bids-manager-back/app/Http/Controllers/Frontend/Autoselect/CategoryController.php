<?php

namespace App\Http\Controllers\Frontend\Autoselect;

use App\Http\Controllers\Controller;
use App\Repositories\V2\Product\CategoryRepository;

/**
 * Class CategoryController
 *
 * @package App\Http\Controllers\Frontend\Autoselect
 */
class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = CategoryRepository::getParentList();

        return response()->api_success($categories);
    }
}
