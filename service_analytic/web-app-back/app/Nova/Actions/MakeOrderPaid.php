<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

use App\Services\Billing\OrderService;
use App\Models\Order;

class MakeOrderPaid extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Активировать заказ';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $orderService = new OrderService;
        foreach (Order::whereIn('id', $models->pluck('id'))->get() as $order) {
            $orderService->activateOrder($order);
            $order->status = Order::SUCCEEDED;
            $order->save();
        }

        return Action::message('Статус заказов изменён на оплаченный');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
