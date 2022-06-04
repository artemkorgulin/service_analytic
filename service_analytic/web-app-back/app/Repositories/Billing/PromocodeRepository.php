<?php
namespace App\Repositories\Billing;

use App\Models\Promocode;
use App\Models\User;

class PromocodeRepository {

    /**
     * Преобразовать промокод в массив
     */
    public function toArray(Promocode $model)
    {
        $basic = [
            'id' => $model->id,
            'code'=> $model->masked_code,
            'type'=>$model->type,
            'discount'=>$model->discount,
            'type_description'=>$model->type_description,
        ];

        $pivot = $model->pivot ? [
            'order_id' => $model->pivot->order_id,
            'is_used' => $model->pivot->order_id ? true : false,
            'created_at' => $model->pivot->created_at,
        ]: [];
        return array_merge($basic, $pivot);
    }

    /**
     * Промокоды у человека
     */
    public function savedFor(User $user)
    {
         return $user->promocodes()->latest()->get();
    }

    /**
     * Получить промокод по его коду
     */
    public function getByCode($code)
    {
        return Promocode::where('code', $code)->first();
    }
}
