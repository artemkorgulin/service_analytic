<?php

namespace App\Http\Controllers;

use App\Models\OzCategory;

class OzProductTypeController extends Controller
{

    /**
     * Получение типов товаров по Ozon
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        return response()->json(OzCategory::select(['id', 'name', 'parent_id'])->whereNotIn(
            'id', OzCategory::select(['parent_id'])
            ->whereNotNull('parent_id')
            ->pluck('parent_id')->toArray())->search($request->search)->paginate());
    }
}
