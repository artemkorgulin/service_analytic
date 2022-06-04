<?php

namespace App\Observers\Nova;

use App\Models\Order;
use App\Services\Billing\OrderService;

/**
 * Наблюдение за заказом в административной панели
 */
class OrderObserver
{
    /**
     * При создании нового заказа,
     * если у него указан тариф, добавляется
     * заранее определённый набор услуг
     */
    public function created(Order $order)
    {
        if ($order->tariff_id) {
            foreach ($order->tariff->services as $service) {
                $order->services()->attach($service->id, ['ordered_amount' => $service->pivot->amount]);
            }
        }
    }
}
