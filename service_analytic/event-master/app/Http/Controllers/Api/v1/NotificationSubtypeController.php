<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationSubtypeCreateRequest;
use App\Models\NotificationSubtype;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class NotificationSubtypeController extends Controller
{
    /**
     * Получить список подтипов.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $notificationSubtypes = NotificationSubtype::all();

            return response()->api_success($notificationSubtypes);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Создать подтип.
     *
     * @param  NotificationSubtypeCreateRequest  $request
     * @return JsonResponse
     */
    public function store(NotificationSubtypeCreateRequest $request): JsonResponse
    {
        try {
            $notificationSubtype = NotificationSubtype::create($request->toArray());

            return response()->api_success($notificationSubtype);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
