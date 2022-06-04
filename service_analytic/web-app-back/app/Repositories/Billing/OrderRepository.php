<?php
namespace App\Repositories\Billing;

use App\Models\User;
use App\Models\Order;

class OrderRepository {

    /**
     * Получить заказ по идентификатору
     */
    public function find(int $id)
    {
        return Order::find($id);
    }

    /**
     * Получить все заказы пользователя
     */
    public function getByUser(User $user)
    {
        return $user->directOrders();
    }

    /**
     * Преобразовать заказ в массив
     */
    public function toArray(Order $model)
    {
        return [
            'id' => $model->id,
            'amount' => (float)$model->amount,
            'type' => $model->type,
            'status' => $model->status,
            'start_at' => $model->start_at,
            'end_at' => $model->end_at,
            'created_at' => $model->created_at,
            'tariff_id' => $model->tariff_id,
        ];
    }
}
