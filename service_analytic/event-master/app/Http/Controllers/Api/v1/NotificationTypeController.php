<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationTypeCreateRequest;
use App\Models\NotificationType;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class NotificationTypeController extends Controller
{
    /**
     * Получить список типов.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $notificationTypes = NotificationType::all();

            return response()->api_success($notificationTypes);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Создать тип.
     *
     * @param  NotificationTypeCreateRequest  $request
     * @return JsonResponse
     */
    public function store(NotificationTypeCreateRequest $request): JsonResponse
    {
        try {
            $notificationType = NotificationType::create($request->toArray());

            return response()->api_success($notificationType);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
