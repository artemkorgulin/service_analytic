<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationSchemaCreateRequest;
use App\Http\Requests\NotificationSchemaIndexRequest;
use App\Models\NotificationSchema;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class NotificationSchemaController extends Controller
{

    /**
     * Получить список настроек пользователя.
     *
     * @param  NotificationSchemaIndexRequest  $request
     * @return JsonResponse
     */
    public function index(NotificationSchemaIndexRequest $request): JsonResponse
    {
        try {
            $userId = $request->input('user')['id'];

            $notificationSchemas = NotificationSchema::where('user_id', $userId)
                ->where('type_id', $request->input('type_id'))
                ->get();

            return response()->api_success($notificationSchemas);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Сохранить настройку пользователя.
     *
     * @param  NotificationSchemaCreateRequest  $request
     * @return JsonResponse
     */
    public function store(NotificationSchemaCreateRequest $request): JsonResponse
    {
        try {
            $data = $request->toArray();

            $userId = $request->input('user')['id'];
            $data['user_ip'] = $request->input('ip');
            $data['user_id'] = $userId;

            if (isset($data['deleted']) && $data['deleted']) {
                $notificationSchema = NotificationSchema::where('user_id', $userId)
                    ->where('type_id', $data['type_id'])
                    ->where('way_code', $data['way_code'])
                    ->firstOrFail();

                $data['delete_user_ip'] = $data['user_ip'];
                unset($data['user_ip']);
                $notificationSchema->fill($data)->save();
                $notificationSchema->delete();
            } else {
                $notificationSchema = NotificationSchema::create($data);
            }

            return response()->api_success($notificationSchema);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
