<?php

namespace App\Services;

use App\Models\Order;
use DateTime;

class NovaService
{

    /**
     * @param  int  $tariffId
     * @param  int  $userId
     *
     * @return Order
     */
    public function createDefaultOrder(int $tariffId, int $userId): Order
    {
        Order::unguard();

        $order = new Order([
            'order_id'             => 0,
            'tariff_ids'           => json_encode([$tariffId]),
            'user_id'              => $userId,
            'type'                 => Order::TYPE_BANK,
            'recipient_id'         => -1,
            'subscription_id'      => -1,
            'amount'               => 0,
            'captured_at'          => new DateTime,
            'period'               => -1,
            'status'               => Order::SUCCEEDED,
            'currency'             => 'RUB',
            'yookassa_id'          => '',
            'description'          => 'Тариф задан через административную панель.',
            'test'                 => false,
            'paid'                 => true,
            'refundable'           => false,
            'receipt_registration' => '',
            'idempotence_key'      => '',
            'link'                 => '',
        ]);

        Order::reguard();

        $order->save();

        return $order;
    }
}
