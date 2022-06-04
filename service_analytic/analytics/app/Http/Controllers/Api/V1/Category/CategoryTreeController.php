<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Controller;
use App\Repositories\V1\Categories\CategoryTreeRepository;

class CategoryTreeController extends Controller
{

    public function index()
    {
        $categories = (new CategoryTreeRepository())->getCachedCategoryTree();
        return response()->api_success(['categories' => $categories['children']]);
    }
}
