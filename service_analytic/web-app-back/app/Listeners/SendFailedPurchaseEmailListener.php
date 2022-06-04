<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendFailedPurchaseEmailListener
 * Слушатель события на неудачную оплату подписки
 * @package App\Listeners
 */
class SendFailedPurchaseEmailListener
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
     * Отправка сообщения о неудачной оплате
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event)
    {
//        $order = $event->order;
//        Mail::send('triggers.payment_failed', ['payment' => $order], function ($message) use ($order) {
//            $message->to($order->subscription->user->email, $order->subscription->user->name)
//                ->subject('Неудачная оплата');
//        });
    }
}
