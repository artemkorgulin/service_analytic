<?php

namespace App\Repositories\Billing;

use App\Models\OldTariff;
use App\Models\Order;
use App\Models\TariffActivity;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserTariffRepository
{
    /**
     * @param User|int $user
     * @return array
     */
    public function getActualTariff(User|int $user): array
    {
        if (is_int($user)) {
            $userId = $user;
        } else {
            $userId = $user->id;
        }

        $order = $this->getActualTariffOrder($userId);

        $result = [];

        if ($order && $order->services) {
            $result['order'] = $order->only('id', 'amount', 'period', 'type', 'captured_at', 'start_at', 'end_at');
            $result['tariff'] = [
                [
                    'tariff_id' => $order->tariff->id,
                    'name' => $order->tariff->name,
                    'description' => $order->tariff->description,
                    'sku' => $order->services()->where('alias', 'semantic')->orderByDesc('ordered_amount')->first()?->pivot->ordered_amount ?? 0,
                    'escrow' => $order->getEscrow(),
                ]
            ];
        } else {
            //todo если не найден новый тариф, ищем старый для пользователя, можно будет удалить со временем
            $result = $this->getOldUserTariff($userId);
        }

        return $result;
    }

    /**
     * @param User|int $user
     * @return array
     */
    public function getTariffDependingContext(User|int $user): array
    {
        if (is_int($user)) {
            $user = User::find($user);
        }

        if ($selectedCompany = $user->getSelectedCompany()) {
            return (new CompanyTariffRepository())->getActualTariff($selectedCompany);
        }

        $tariff = $this->getActualTariff($user);
        $tariff['is_corporate'] = false;

        return $tariff;
    }

    /**
     * @param int $userId
     * @return Order|null
     */
    public function getActualTariffOrder(int $userId): Order|null
    {
        return Order::where('user_id', $userId)
            ->whereHas('tariff')
            ->whereNull('company_id')
            ->where('status', '=', 'succeeded')
            ->where('start_at', '<=', Carbon::now())
            ->where('end_at', '>=', Carbon::now())
            ->orderByDesc('captured_at')
            ->first();
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getOldUserTariff(int $userId): array
    {
        $tariffActivity = TariffActivity::query()
            ->select('tariff_id', 'order_id', 'end_at')
            ->where('user_id', '=', $userId)
            ->where('status', '=', TariffActivity::ACTVE)
            ->whereExists(function ($query) use ($userId) {
                $query->select('id')
                    ->from('orders')
                    ->whereNotNull('captured_at')
                    ->where('user_id', $userId)
                    ->whereRaw('orders.id = tariff_activity.order_id');
            })
            ->first();

        return OldTariff::getTariffsActivity($tariffActivity);
    }
}
