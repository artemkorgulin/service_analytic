<?php

namespace App\Http\Controllers\Api\v1\Yookassa;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\FtpService;
use App\Services\V2\PaymentService;
use App\Services\V2\TariffActivityService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;
use YooKassa\Model\Notification\NotificationCanceled;
use YooKassa\Model\Notification\NotificationRefundSucceeded;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\PaymentStatus;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use App\Services\UserService;

/**
 * Class YookassaController
 * Обработка хуков от Yookassa
 * @package App\Http\Controllers\Api\v1\Yookassa
 */
class YookassaController extends Controller
{
    const TEST_USER = 15;

    /**
     * Обработка входящих уведомлений
     *
     * @param  Request  $request
     * @return void
     */
    public function handleEvent(Request $request)
    {
        $data = $request->all();

        Log::channel('yokassa_hooks')->info('Received hook ', $data);
        try {
            switch ($data['event']) {
                case NotificationEventType::PAYMENT_SUCCEEDED:
                    $notification = new NotificationSucceeded($data);
                    break;
                case NotificationEventType::PAYMENT_CANCELED:
                    $notification = new NotificationCanceled($data);
                    break;
                case NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE:
                    $notification = new NotificationWaitingForCapture($data);
                    break;
                case NotificationEventType::REFUND_SUCCEEDED:
                    $notification = new NotificationRefundSucceeded($data);
                    break;
                default:
                    Log::channel('yokassa_hooks')->error('Undefined event', $data);
                    return;
            }
        } catch (Throwable $exception) {
            report($exception);
            Log::channel('yokassa_hooks')->error($exception->getMessage(), $data);
            return;
        }
        $obj = $notification->getObject();
        $order = Order::where('yookassa_id', $obj->getId())->first();

        if (!$order) {
            Log::channel('yokassa_hooks')->error('Payment id not found', $data);
            return response()->api_success([]);
        }

        if ($obj->getStatus() === $order->status) {
            Log::channel('yokassa_hooks')->error('Duplicate status hook', $data);
            return response()->api_success([]);
        }

        $user = User::find($order->user_id);
        $paymentService = new PaymentService($user, $order);
        $paymentService->updateOrder($obj);
        if ($obj->getStatus() === PaymentStatus::SUCCEEDED) {
            $tariffActivityService = new TariffActivityService($order);
            $tariffActivityService->activateSubscription();

            if ($order->company) {
                foreach ($order->company->users as $companyUser) {
                    (new UserService($companyUser))->forgetAccountsCache();
                }
            } else {
                (new UserService($user))->forgetAccountsCache();
            }

            // Отправляем данные на ФТП
            (new FtpService())->putCardToFtp($order,);
            Log::channel('yokassa_hooks')->info('billing.card_success',
                ['orderId' => $order->id, 'userId' => $order->user_id, 'amount' => $order->amount,]);

            UsersNotification::dispatch(
                'billing.card_success',
                [
                    ['id' => $user->id, 'fio' => $user->name, 'email' => $user->email, 'lang' => 'ru'],
                    ['id' => self::TEST_USER, 'fio' => $user->name, 'email' => 's.garbusha@yandex.ru', 'lang' => 'ru'],
                ],
                ['order' => [['title' => 'Успешная оплата пакета на сумму', 'price' => $order->amount]]]
            );
        }

        return response()->api_success([]);
    }
}
