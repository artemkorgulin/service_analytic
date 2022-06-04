<?php
namespace App\Repositories\Billing;

use App\Models\Tariff;

class TariffRepository {

    /**
     * Получить тариф по номеру
     */
    public function find(int $id)
    {
        return Tariff::find($id);
    }

    /**
     * Получить доступные для отображения тарифы
     */
    public function getVisible()
    {
        return Tariff::visible();
    }

    /**
     * Преобразовать модель в массив
     */
    public function toArray(Tariff $model)
    {
       return [
           'id' => $model->id,
           'name' => $model->name,
           'alias' => $model->alias,
           'description' => $model->description,
           'price' => $model->humanPrice,
           'services' => $model->services->map(function($item){
                return [
                   'id' => $item->id,
                   'name' => $item->name,
                   'alias' => $item->alias,
                   'min_order_amount' => $item->min_order_amount,
                   'max_order_amount' => $item->max_order_amount,
                   'countable' => $item->countable,
                   'sellable' => $item->sellable,
                   'description' => $item->description,
                   'amount' => $item->pivot->amount,
               ];
           })
       ];
    }
}
