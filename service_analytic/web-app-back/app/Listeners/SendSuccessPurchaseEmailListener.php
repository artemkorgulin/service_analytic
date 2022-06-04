<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use YooKassa\Model\PaymentStatus;

/**
 * Class SendSuccessPurchaseEmailListener
 * Слушатель события на оплату подписки
 * @package App\Listeners
 */
class SendSuccessPurchaseEmailListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Отправка сообщения об успешной оплате
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event)
    {
//        $order = $event->order;
//        Mail::send('triggers.payment_success', ['payment' => $order], function ($message) use ($order) {
//            $message->to($order->subscription->user->email, $order->subscription->user->name)
//                ->subject('Успешная оплата');
//        });
    }
}
