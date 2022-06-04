<?php

namespace App\Repositories\Billing;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Support\Carbon;

class CompanyTariffRepository
{
    /**
     * @param Company|int $company
     * @return array
     */
    public function getActualTariff(Company|int $company): array
    {
        if (is_int($company)) {
            $companyId = $company;
        } else {
            $companyId = $company->id;
        }

        $order = $this->getActualTariffOrder($companyId);

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
        }

        return $result;
    }

    /**
     * @param int $companyId
     * @return Order|null
     */
    public function getActualTariffOrder(int $companyId): Order|null
    {
        return Order::where('company_id', $companyId)
            ->whereHas('tariff')
            ->where('status', '=', 'succeeded')
            ->where('start_at', '<=', Carbon::now())
            ->where('end_at', '>=', Carbon::now())
            ->orderByDesc('captured_at')
            ->first();
    }
}
