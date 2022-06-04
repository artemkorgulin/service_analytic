<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Billing\OrderService;
use App\Services\CalculateService;

class BillingController extends Controller
{
    /**
     * Получить информацию по тарифу и услугам заказанным
     */
    public function information(CalculateService $calculateService, OrderService $orderService)
    {
        $user = auth()->user();

        return response()->api_success([
            'tariff' => $orderService->currentUserTariff($user),
            'is_free' => $orderService->isFreeTariff($user),
            'services' => $orderService->activeServices($user)
        ]);
    }

    /**
     * Получить информацию по скидкам к заказу
     */
    public function discounts(CalculateService $calculateService)
    {
        return response()->api_success(
            $calculateService->discountsInformation(auth()->user())
        );
    }
}
