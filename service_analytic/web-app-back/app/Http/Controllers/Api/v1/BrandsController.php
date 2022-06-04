<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\dist\brands\CompareBrand;
use App\Http\Controllers\Api\v1\dist\brands\MergeAllBrands;
use App\Models\BrandBlackList;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\dist\blackList\BlackList;

/**
 *
 */
class BrandsController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBrands(Request $request): JsonResponse
    {
        return response()->json((new MergeAllBrands())->setRequest($request)->get());
    }

    /**
     * @throws Exception
     */
    public function getBrand(Request $request): JsonResponse
    {
        return response()->json((new CompareBrand)->setRequest($request)->compare());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getBrandAll(Request $request): JsonResponse
    {
        if(!$request->id)
            throw new Exception('Не передан id позиции черного списка');
        return response()->json(BrandBlackList::query()->where(['id' => $request->id])->first());
    }
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function patchBrand(Request $request): JsonResponse
    {
        if(!$request->id)
            throw new Exception('Не передан id позиции черного списка');

        $request->validate([
            'brand' => 'required|string',
            'manager' => 'required|string',
            'status' => 'required|string',
        ]);
        if(!$obj = BrandBlackList::query()->where(['id' => $request->id])->first())
            throw new Exception('Не найдена текущая позиция');

        $obj->partner = $request->partner;
        $obj->brand = $request->brand;
        $obj->manager = $request->manager;
        $obj->status = $request->status;
        $obj->wb = $request->wb ?? 0;
        $obj->ozon = $request->ozon ?? 0;
        $obj->pattern = $request->pattern;
        $obj->save();

        return response()->json(BrandBlackList::query()->where(['id' => $request->id])->first());
    }


    /**
     * @throws Exception
     */
    public function deleteBrand(Request $request): JsonResponse
    {
        if(!$request->id)
            throw new Exception('Не передан id позиции черного списка');
        BrandBlackList::query()->where(['id' => $request->id])->delete();
        return response()->json(BrandBlackList::query()->paginate(20));
    }

    /**
     * @throws Exception
     */
    public function createBrand(Request $request): JsonResponse
    {

        $request->validate([
            'brand' => 'required|string',
            'manager' => 'required|string',
            'status' => 'required|string',
        ]);

        $id = BrandBlackList::query()->create([
            'partner' => $request->partner,
            'brand' => $request->brand,
            'manager' => $request->manager,
            'status' => $request->status,
            'wb' => $request->wb ?? 0,
            'ozon' => $request->ozon ?? 0,
            'pattern' => $request->pattern
        ]);

        return response()->json('Добавлен новый бренд');
    }
}
