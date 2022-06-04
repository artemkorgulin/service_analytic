<?php


namespace App\Http\Controllers\Api\inner;

use App\Http\Controllers\Controller;
use App\Services\CounterService;
use Illuminate\Http\JsonResponse;

class InnerCountersController extends Controller
{
    /**
     * Получение количества пользователей + 3500
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLendingCountUsers():JsonResponse
    {
        return response()->json( CounterService::setCountUsersCache());
    }
}
