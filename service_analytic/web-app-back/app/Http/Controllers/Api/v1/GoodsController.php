<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\dist\blackList\BlackListPermission;
use App\Http\Controllers\Controller;
use App\Models\BrandBlackList;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * api по товарам
 */
class GoodsController  extends Controller
{

    /**
     * Получить товары пользователя по ozon
     * @return JsonResponse
     * @throws Exception
     */
    public function getGoodsUserOz(Request $request): JsonResponse
    {
        if(empty($request->userId))
            throw new Exception('не передан id пользователя');

        if(!empty($request->accountId))
            $oz = \DB::connection('va')->table('oz_products as op')->select(['op.id', 'op.name', 'op.url', 'op.price', 'op.photo_url'])->where(['op.user_id' => $request->userId, 'op.account_id' => $request->accountId])->paginate(10);
        else
            $oz = \DB::connection('va')->table('oz_products as op')->select(['op.id', 'op.name', 'op.url', 'op.price', 'op.photo_url'])->where(['op.user_id' => $request->userId])->paginate(10);

        return response()->json($oz);
    }

    /**
     * Получить товары пользователя по wildberries
     * @return JsonResponse
     * @throws Exception
     */
    public function getGoodsUserWb(Request $request): JsonResponse
    {
        if(empty($request->userId))
            throw new Exception('не передан id пользователя');

        if(!empty($request->accountId))
            $wb = \DB::connection('va')->table('wb_products as wb')->select(['wb.id', 'wb.title', 'wb.url', 'wb.price_range', 'wb.imt_id'])->where(['wb.user_id' => $request->userId, 'wb.account_id' => $request->accountId])->paginate(10);
        else
            $wb = \DB::connection('va')->table('wb_products as wb')->select(['wb.id', 'wb.title', 'wb.url', 'wb.price_range', 'wb.imt_id'])->where(['wb.user_id' => $request->userId])->paginate(10);


        return response()->json($wb);
    }

    /**
     * Получить черный список
     * @param Request $request
     * @return JsonResponse
     */
    public function getBlackList(Request $request): JsonResponse
    {

        return response()->json(BrandBlackList::query()->paginate(20));

    }

    /**
     * Редактировать черный список
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function editBlackList(Request $request): JsonResponse
    {

        (new BlackListPermission())->setRequest($request)->change();

        return response()->json(BrandBlackList::query()->paginate(20));

    }




}
