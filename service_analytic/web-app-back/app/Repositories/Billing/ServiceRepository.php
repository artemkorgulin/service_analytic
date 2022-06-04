<?php

namespace App\Repositories\Billing;

use App\Models\Service;

class ServiceRepository
{
    /**
     * Получить услугу по номеру
     */
    public function find(int $id)
    {
        return Service::find($id);
    }

    /**
     * Получить услугу по коду
     */
    public function findByAlias(string $alias)
    {
        return Service::where('alias', $alias)->first();
    }

    /**
     * Получить доступные для отображения услуги
     */
    public function getVisible()
    {
        return Service::visible();
    }

    public function toArray($model)
    {
        return [
           'id' => $model->id,
           'name' => $model->name,
           'alias' => $model->alias,
           'description' => $model->description,
           'min_order_amount' => $model->min_order_amount,
           'max_order_amount' => $model->max_order_amount,
           'countable' => $model->countable,
           'sellable' => $model->sellable,
           'price' => $model->prices->map(function ($item) {
               return [
                   'min_amount' => $item->min_amount,
                   'discount' => $item->discount(),
                   'price_per_item' => $item->humanPricePerItem
               ];
           })
        ];
    }
}
