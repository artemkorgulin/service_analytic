<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationTemplateCreateRequest;
use App\Http\Requests\NotificationTemplateUpdateRequest;
use App\Models\NotificationTemplate;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class NotificationTemplateController extends Controller
{
    /**
     * Получить список шаблонов.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $notificationTemplates = NotificationTemplate::all();

            return response()->api_success($notificationTemplates);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Создать шаблон.
     *
     * @param  NotificationTemplateCreateRequest  $request
     * @return JsonResponse
     */
    public function store(NotificationTemplateCreateRequest $request): JsonResponse
    {
        try {
            $notificationTemplate = NotificationTemplate::create($request->toArray());

            return response()->api_success($notificationTemplate);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Обновить шаблон.
     *
     * @param  NotificationTemplate  $notificationTemplate
     * @param  NotificationTemplateUpdateRequest  $request
     * @return JsonResponse
     */
    public function update(NotificationTemplate $notificationTemplate, NotificationTemplateUpdateRequest $request): JsonResponse
    {
        try {
            $notificationTemplate->fill($request->toArray())->save();

            return response()->api_success($notificationTemplate);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Удалить шаблон.
     *
     * @param  NotificationTemplate  $notificationTemplate
     * @return JsonResponse
     */
    public function destroy(NotificationTemplate $notificationTemplate): JsonResponse
    {
        try {
            $notificationTemplate->delete();

            return response()->api_success($notificationTemplate);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Посмотреть шаблон.
     *
     * @return JsonResponse
     */
    public function show(NotificationTemplate $notificationTemplate): JsonResponse
    {
        try {
            return response()->api_success($notificationTemplate);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
