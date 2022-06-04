<?php

namespace App\Http\Controllers\Api\v1;

use App\Classes\EventHandlers\EventHandler;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationCreateRequest;
use App\Http\Requests\NotificationIndexRequest;
use App\Http\Requests\NotificationMakeReadRequest;
use App\Models\Notification;
use App\Models\NotificationUser;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * @param  EventHandler  $eventHandler
     */
    public function __construct(private EventHandler $eventHandler)
    {
    }

    /**
     * Получить список уведомлений пользователя.
     *
     * @param  NotificationIndexRequest  $request
     * @return JsonResponse
     */
    public function index(NotificationIndexRequest $request): JsonResponse
    {
        try {
            $user_id = $request->input('user')['id'];
            $is_active = $request->input('is_active');
            $orderByDesc = (bool) $request->input('order_by_desc');
            $limit = (int) $request->input('limit');
            $perPage = $request->input('perPage');
            $currentPage = $request->input('currentPage');

            $notifications = Notification::whereHas('users', function ($q) use ($user_id, $is_active) {
                $q->where('user_id', $user_id);
                if ($is_active) {
                    $q->where('is_read', false);
                }
            });

            if ($orderByDesc) {
                $notifications->orderBy('created_at', 'DESC');
            } else {
                $notifications->orderBy('created_at');
            }

            if ($perPage) {
                $result = $notifications->paginate($perPage, ['*'], 'page',
                    $currentPage ?? 1);
            } else {
                if ($limit) {
                    $notifications->take($limit);
                }

                $result = $notifications->get();
            }

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Создать уведомление.
     *
     * @param  NotificationCreateRequest  $request
     * @return JsonResponse
     */
    public function store(NotificationCreateRequest $request): JsonResponse
    {
        $users = [];
        foreach ($request->input('users') as $user) {
            $users[$user['id']] = $user;
        }
        $event = $request->input('event_code');
        $data = $request->all();

        try {
            $this->eventHandler->handle($event, $users, $data);

            return response()->api_success([]);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Сделать оповещение прочитанным.
     *
     * @param  NotificationMakeReadRequest  $request
     * @return JsonResponse
     */
    public function makeRead(NotificationMakeReadRequest $request): JsonResponse
    {
        try {
            $user_id = $request->input('user')['id'];
            $notificationUser = NotificationUser::where('user_id', $user_id)
                ->where('notification_id', $request->input('notification_id'))
                ->firstOrFail();

            $result = NotificationUser::where('user_id', $user_id)
                ->where('id', '<=', $notificationUser->id)
                ->update(['is_read' => 1]);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
